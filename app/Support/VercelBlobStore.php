<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class VercelBlobStore
{
    public function ownsUrl(string $url, string $folder): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        return parse_url($url, PHP_URL_SCHEME) === 'https'
            && is_string($host)
            && str_ends_with(strtolower($host), '.public.blob.vercel-storage.com')
            && is_string($path)
            && str_starts_with($path, '/uploads/'.$folder.'/');
    }

    public function delete(string $url): void
    {
        $token = config('services.vercel_blob.internal_token');
        $deploymentUrl = config('services.vercel_blob.deployment_url');

        if (! is_string($token) || trim($token) === '' || ! is_string($deploymentUrl) || trim($deploymentUrl) === '') {
            throw new RuntimeException('Vercel Blob deletion is not configured.');
        }

        $baseUrl = str_starts_with($deploymentUrl, 'http')
            ? rtrim($deploymentUrl, '/')
            : 'https://'.rtrim($deploymentUrl, '/');

        Http::withToken($token)
            ->connectTimeout(3)
            ->timeout(10)
            ->post($baseUrl.'/api/blob-delete', ['url' => $url])
            ->throw();
    }
}
