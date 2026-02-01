<?php

namespace BenColmer\LaravelFITValidator;

use BenColmer\LaravelFITValidator\Contracts\FITValidator as FITValidatorContract;
use BenColmer\LaravelFITValidator\Exceptions\ConfigurationException;
use BenColmer\LaravelFITValidator\Exceptions\ValidationException;
use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Validate Forge Invocation Tokens (FITs) for an app.
 *
 * @see https://developer.atlassian.com/platform/forge/remote/essentials
 */
class FITValidator implements FITValidatorContract
{
    /**
     * The app ID.
     */
    protected string $appId;

    /**
     * The JSON Web Key Set url for the app.
     */
    protected string $jwksUrl;

    public function __construct(string $appKey = 'default')
    {
        $this->appId = (string) Config::get("fit.applications.{$appKey}.appId");
        if (! $this->appId) throw new ConfigurationException($appKey, 'appId');

        $this->jwksUrl = (string) Config::get("fit.applications.{$appKey}.jwksUrl");
        if (! $this->jwksUrl) throw new ConfigurationException($appKey, 'jwksUrl');
    }

    public function validate(Request|string $input): ?array
    {
        $jwt = is_string($input) ? $input : $this->parseToken($input);
        if (! is_string($jwt)) {
            throw new ValidationException('JWT is missing from the validation input.');
        }

        $jwks = $this->fetchJwks();
        if (! $jwks) {
            throw new ValidationException('Failed to fetch JWKS.');
        }

        try {
            // validate and decode JWT
            $payload = (array) JWT::decode($jwt, JWK::parseKeySet($jwks));

            // check audience and issuer claims
            $additionalClaims = [
                'aud' => $this->appId,
                'iss' => Config::get('fit.issuer'),
            ];
            foreach ($additionalClaims as $claim => $expected) {
                $actual = (string) ($payload[$claim] ?? '');

                if (! hash_equals($expected, $actual)) {
                    $msg = "FIT payload contained unexpected \"{$claim}\" value";
                    throw new Exception($msg);
                }
            }

            return $payload;
        } catch (Exception $e) {
            Log::warning('FIT failed validation.');

            throw new ValidationException($e);
        }
    }

    /**
     * Prase the FIT from the request authorization header.
     */
    protected function parseToken(Request $request): ?string
    {
        $auth = (string) $request->header('authorization');

        // expected format is "Bearer {FIT}"
        $parts = explode(' ', $auth, 2);
        return isset($parts[1]) && is_string($parts[1]) ? $parts[1] : null;
    }

    /**
     * Fetch the JSON Web Key Set from the JWKS URL.
     */
    protected function fetchJwks(): ?array
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
}
