<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use UnexpectedValueException;

class JsonStore
{
    private array $githubRecords = [];

    private array $githubShas = [];

    private bool $githubRepositoryChecked = false;

    private const FIELDS = [
        'users' => ['name', 'email', 'password', 'level_akses', 'remember_token', 'email_verified_at'],
        'pembinas' => ['nama', 'jabatan', 'image_url'],
        'pimpinans' => ['nama', 'posisi', 'prodi', 'image_url'],
        'units' => ['nama', 'slug', 'kategori', 'deskripsi', 'foto_bersama'],
        'anggotas' => ['unit_id', 'nama', 'jabatan', 'prodi', 'deskripsi', 'image_url'],
        'site_contents' => [
            'sejarah', 'visi', 'misi', 'join_title', 'join_description', 'join_label', 'join_url',
            'show_join_menu', 'contact_email', 'contact_phone', 'contact_address', 'instagram_url',
            'youtube_url', 'tiktok_url', 'hero_image',
        ],
        'publications' => ['kategori', 'judul', 'deskripsi', 'tanggal', 'image_url'],
    ];

    public function all(string $collection): Collection
    {
        return collect($this->read($collection))
            ->map(fn (array $record) => (object) $this->normalize($collection, $record));
    }

    public function find(string $collection, int|string $id): ?object
    {
        return $this->all($collection)->first(
            fn (object $record) => (string) $record->id === (string) $id,
        );
    }

    public function first(string $collection): ?object
    {
        return $this->all($collection)->first();
    }

    public function firstWhere(string $collection, string $key, mixed $value): ?object
    {
        return $this->all($collection)->first(
            fn (object $record) => ($record->{$key} ?? null) === $value,
        );
    }

    public function create(string $collection, array $attributes): object
    {
        return $this->withLock($collection, LOCK_EX, function () use ($collection, $attributes): object {
            $records = $this->readUnlocked($collection);

            return $this->appendRecord($collection, $records, $attributes);
        });
    }

    public function updateUnique(
        string $collection,
        int|string $id,
        string $field,
        mixed $value,
        array $attributes,
    ): object {
        return $this->withLock($collection, LOCK_EX, function () use ($collection, $id, $field, $value, $attributes): object {
            $records = $this->readUnlocked($collection);

            foreach ($records as $record) {
                if ((string) ($record['id'] ?? '') !== (string) $id && ($record[$field] ?? null) === $value) {
                    throw new \DomainException("A record with [{$field}] [{$value}] already exists in [{$collection}].");
                }
            }

            return $this->updateUnlocked($collection, $id, $attributes, $records);
        });
    }

    public function createUnique(string $collection, string $field, mixed $value, array $attributes): object
    {
        return $this->withLock($collection, LOCK_EX, function () use ($collection, $field, $value, $attributes): object {
            $records = $this->readUnlocked($collection);

            foreach ($records as $record) {
                if (($record[$field] ?? null) === $value) {
                    throw new \DomainException("A record with [{$field}] [{$value}] already exists in [{$collection}].");
                }
            }

            return $this->appendRecord($collection, $records, $attributes);
        });
    }

    public function update(string $collection, int|string $id, array $attributes): object
    {
        return $this->withLock(
            $collection,
            LOCK_EX,
            fn () => $this->updateUnlocked($collection, $id, $attributes),
        );
    }

    public function delete(string $collection, int|string $id): bool
    {
        return $this->withLock($collection, LOCK_EX, function () use ($collection, $id): bool {
            $records = $this->readUnlocked($collection);
            $remaining = array_values(array_filter(
                $records,
                fn (array $record) => (string) ($record['id'] ?? '') !== (string) $id,
            ));

            if (count($remaining) === count($records)) {
                return false;
            }

            $this->writeUnlocked($collection, $remaining);

            return true;
        });
    }

    public function replace(string $collection, array $records): void
    {
        foreach ($records as $record) {
            if (! is_array($record) || ! isset($record['id'])) {
                throw new UnexpectedValueException("Every record in [{$collection}] must have an id.");
            }
        }

        $this->withLock($collection, LOCK_EX, function () use ($collection, $records): void {
            $this->writeUnlocked($collection, array_map(
                fn (array $record) => $this->normalize($collection, $record),
                array_values($records),
            ));
        });
    }

    private function read(string $collection): array
    {
        return $this->withLock($collection, LOCK_SH, fn () => $this->readUnlocked($collection));
    }

    private function readUnlocked(string $collection): array
    {
        if (config('data.driver') === 'github') {
            return $this->readFromGithub($collection);
        }

        $path = $this->path($collection);

        if (! is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read JSON data file [{$path}].");
        }

        $records = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($records) || ! array_is_list($records)) {
            throw new UnexpectedValueException("JSON data file [{$path}] must contain a list of records.");
        }

        foreach ($records as $record) {
            if (! is_array($record)) {
                throw new UnexpectedValueException("JSON data file [{$path}] contains an invalid record.");
            }
        }

        return $records;
    }

    private function writeUnlocked(string $collection, array $records): void
    {
        if (config('data.driver') === 'github') {
            $this->writeToGithub($collection, $records);

            return;
        }

        $path = $this->path($collection);
        $json = json_encode(
            array_values($records),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        if (file_put_contents($path, $json.PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException("Unable to write JSON data file [{$path}].");
        }
    }

    private function withLock(string $collection, int $operation, callable $callback): mixed
    {
        $path = $this->path($collection);
        $lock = fopen($path.'.lock', 'c');

        if ($lock === false) {
            throw new RuntimeException("Unable to open JSON data lock for [{$collection}].");
        }

        try {
            if (! flock($lock, $operation)) {
                throw new RuntimeException("Unable to lock JSON data collection [{$collection}].");
            }

            return $callback();
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function path(string $collection): string
    {
        if (! array_key_exists($collection, self::FIELDS)) {
            throw new UnexpectedValueException("Unknown JSON data collection [{$collection}].");
        }

        $directory = config('data.path', storage_path('app/data'));

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create JSON data directory [{$directory}].");
        }

        return $directory.DIRECTORY_SEPARATOR.$collection.'.json';
    }

    private function readFromGithub(string $collection): array
    {
        if (array_key_exists($collection, $this->githubRecords)) {
            return $this->githubRecords[$collection];
        }

        $this->ensureGithubRepositoryIsAccessible();

        $response = $this->githubRequest()->get($this->githubContentsUrl($collection), [
            'ref' => config('services.github.data_branch', 'main'),
        ]);

        if ($response->notFound()) {
            $this->githubShas[$collection] = null;
            $this->githubRecords[$collection] = [];

            return [];
        }

        $data = $response->throw()->json();
        $content = is_array($data) ? ($data['content'] ?? null) : null;
        $sha = is_array($data) ? ($data['sha'] ?? null) : null;

        if (! is_string($content) || ! is_string($sha)) {
            throw new UnexpectedValueException("GitHub returned an invalid JSON file response for [{$collection}].");
        }

        $decodedContent = base64_decode(str_replace(["\r", "\n"], '', $content), true);

        if ($decodedContent === false) {
            throw new UnexpectedValueException("GitHub returned invalid base64 content for [{$collection}].");
        }

        $records = json_decode($decodedContent, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($records) || ! array_is_list($records)) {
            throw new UnexpectedValueException("GitHub JSON file [{$collection}] must contain a list of records.");
        }

        foreach ($records as $record) {
            if (! is_array($record)) {
                throw new UnexpectedValueException("GitHub JSON file [{$collection}] contains an invalid record.");
            }
        }

        $this->githubShas[$collection] = $sha;
        $this->githubRecords[$collection] = $records;

        return $records;
    }

    private function writeToGithub(string $collection, array $records): void
    {
        $this->ensureGithubRepositoryIsAccessible();

        if (! array_key_exists($collection, $this->githubRecords)) {
            $this->readFromGithub($collection);
        }

        $content = json_encode(
            array_values($records),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ).PHP_EOL;
        $payload = [
            'message' => "Update {$collection}.json via HMVE website",
            'content' => base64_encode($content),
            'branch' => config('services.github.data_branch', 'main'),
        ];

        if ($this->githubShas[$collection] !== null) {
            $payload['sha'] = $this->githubShas[$collection];
        }

        $response = $this->githubRequest()->put($this->githubContentsUrl($collection), $payload)->throw();
        $sha = $response->json('content.sha');

        if (! is_string($sha)) {
            throw new UnexpectedValueException("GitHub did not return a file SHA after saving [{$collection}].");
        }

        $this->githubShas[$collection] = $sha;
        $this->githubRecords[$collection] = array_values($records);
    }

    private function ensureGithubRepositoryIsAccessible(): void
    {
        if ($this->githubRepositoryChecked) {
            return;
        }

        $repository = $this->githubRepository();
        $this->githubRequest()
            ->get(rtrim(config('services.github.api_url', 'https://api.github.com'), '/').'/repos/'.$repository)
            ->throw();

        $this->githubRepositoryChecked = true;
    }

    private function githubRequest(): PendingRequest
    {
        $token = config('services.github.data_token');

        if (! is_string($token) || trim($token) === '') {
            throw new RuntimeException('The GitHub data token is not configured.');
        }

        return Http::withToken($token)
            ->acceptJson()
            ->withHeaders([
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->connectTimeout(3)
            ->timeout(10);
    }

    private function githubRepository(): string
    {
        $repository = trim((string) config('services.github.data_repository'));

        if (! preg_match('~\A[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+\z~D', $repository)) {
            throw new RuntimeException('The GitHub data repository must use the owner/repository format.');
        }

        return $repository;
    }

    private function githubContentsUrl(string $collection): string
    {
        $directory = trim((string) config('services.github.data_directory', 'data'), '/');
        $segments = $directory === '' ? [] : explode('/', $directory);

        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..' || ! preg_match('/\A[A-Za-z0-9_.-]+\z/D', $segment)) {
                throw new RuntimeException('The GitHub data directory contains an invalid path segment.');
            }
        }

        $segments[] = $collection.'.json';
        $path = implode('/', array_map('rawurlencode', $segments));

        return rtrim(config('services.github.api_url', 'https://api.github.com'), '/')
            .'/repos/'.$this->githubRepository().'/contents/'.$path;
    }

    private function normalize(string $collection, array $record): array
    {
        $defaults = array_fill_keys(self::FIELDS[$collection], null);

        if ($collection === 'site_contents') {
            $defaults['show_join_menu'] = false;
        } elseif ($collection === 'users') {
            $defaults['level_akses'] = 1;
        }

        return array_replace($defaults, $record);
    }

    private function appendRecord(string $collection, array $records, array $attributes): object
    {
        $nextId = collect($records)->max('id') + 1;
        $now = now()->toDateTimeString();
        $record = $this->normalize($collection, [
            ...$attributes,
            'id' => $nextId,
            'created_at' => $attributes['created_at'] ?? $now,
            'updated_at' => $attributes['updated_at'] ?? $now,
        ]);

        $records[] = $record;
        $this->writeUnlocked($collection, $records);

        return (object) $record;
    }

    private function updateUnlocked(string $collection, int|string $id, array $attributes, ?array $records = null): object
    {
        $records ??= $this->readUnlocked($collection);

        foreach ($records as $index => $record) {
            if ((string) ($record['id'] ?? '') !== (string) $id) {
                continue;
            }

            $records[$index] = $this->normalize($collection, [
                ...$record,
                ...$attributes,
                'id' => $record['id'],
                'updated_at' => now()->toDateTimeString(),
            ]);
            $this->writeUnlocked($collection, $records);

            return (object) $records[$index];
        }

        throw new RuntimeException("Record [{$id}] was not found in [{$collection}].");
    }
}
