<?php

namespace Tests\Feature;

use App\Support\JsonStore;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GithubJsonStoreTest extends TestCase
{
    public function test_private_json_collection_is_read_from_github(): void
    {
        $this->configureGithub();
        Http::preventStrayRequests();
        Http::fake([
            'api.github.com/repos/hmve/data-repo' => Http::response(['private' => true]),
            'api.github.com/repos/hmve/data-repo/contents/data/units.json*' => Http::response([
                'sha' => 'file-sha',
                'content' => base64_encode('[{"id":1,"nama":"Biro HMVE"}]'),
            ]),
        ]);

        $unit = app(JsonStore::class)->find('units', 1);

        $this->assertSame('Biro HMVE', $unit->nama);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.github.com/repos/hmve/data-repo'
            && $request->hasHeader('Authorization', 'Bearer test-github-token'));
    }

    public function test_new_records_are_committed_to_the_private_repository(): void
    {
        $this->configureGithub();
        Http::preventStrayRequests();
        Http::fake([
            'api.github.com/*' => Http::sequence()
                ->push(['private' => true])
                ->push([
                    'sha' => 'old-sha',
                    'content' => base64_encode('[]'),
                ])
                ->push(['content' => ['sha' => 'new-sha']]),
        ]);

        $unit = app(JsonStore::class)->create('units', [
            'nama' => 'Biro HMVE',
            'slug' => 'biro-hmve',
        ]);

        $this->assertSame(1, $unit->id);
        Http::assertSent(fn (Request $request) => $request->method() === 'PUT'
            && $request->url() === 'https://api.github.com/repos/hmve/data-repo/contents/data/units.json'
            && $request->data()['sha'] === 'old-sha'
            && str_contains(base64_decode($request->data()['content']), '"nama": "Biro HMVE"'));
    }

    public function test_concurrent_repository_update_is_reported_instead_of_overwriting_newer_data(): void
    {
        $this->configureGithub();
        Http::preventStrayRequests();
        Http::fake([
            'api.github.com/*' => Http::sequence()
                ->push(['private' => true])
                ->push([
                    'sha' => 'old-sha',
                    'content' => base64_encode('[]'),
                ])
                ->push(['message' => 'sha does not match'], 409),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        app(JsonStore::class)->create('units', [
            'nama' => 'Biro HMVE',
            'slug' => 'biro-hmve',
        ]);
    }

    private function configureGithub(): void
    {
        config([
            'data.driver' => 'github',
            'services.github.api_url' => 'https://api.github.com',
            'services.github.data_repository' => 'hmve/data-repo',
            'services.github.data_token' => 'test-github-token',
            'services.github.data_branch' => 'main',
            'services.github.data_directory' => 'data',
        ]);
    }
}
