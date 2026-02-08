<?php

namespace BenColmer\LaravelFITValidator;

use BenColmer\LaravelFITValidator\Contracts\FITKeySetClient as FITKeySetClientContract;
use BenColmer\LaravelFITValidator\Exceptions\ConfigurationException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Fetch the JSON Web Key Set (JWKS) for an app.
 */
class FITKeySetClient implements FITKeySetClientContract
{
    /**
     * The JSON Web Key Set url.
     */
    protected string $jwksUrl;

    public function __construct(string $appKey = 'default')
    {
        $this->jwksUrl = (string) Config::get("fit.applications.{$appKey}.jwksUrl");
        if (! $this->jwksUrl) throw new ConfigurationException($appKey, 'jwksUrl');
    }

    public function get(): ?array
    {
        $duration = Config::get('fit.jwksCacheDuration', 60 * 5);
        if (! $duration) return $this->fetchJwks();

        return Cache::remember(
            $this->cacheKey(),
            $duration,
            fn() => $this->fetchJwks()
        );
    }

    /**
     * Fetch the JSON Web Key Set from the JWKS URL.
     */
    protected function fetchJwks()
    {
        $client = new Client();
        $response = $client->get($this->jwksUrl, [
            'http_errors' => false
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            Log::error('{statusCode} HTTP status code encountered while fetching JWKS URL from "{url}".', [
                'statusCode' => $statusCode,
                'url' => $this->jwksUrl,
            ]);

            return null;
        }

        $jwks = json_decode((string) $response->getBody(), true);
        if (! isset($jwks['keys'])) {
            Log::error('Failed to parse JWKS fetched from "{url}".', [
                'url' => $this->jwksUrl,
            ]);

            return null;
        }

        return $jwks;
    }

    protected function cacheKey(): string
    {
        return 'fit_jwks_' . hash('sha256', $this->jwksUrl);
    }
}
