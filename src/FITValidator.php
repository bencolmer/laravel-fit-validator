<?php

namespace BenColmer\LaravelFITValidator;

use BenColmer\LaravelFITValidator\Contracts\FITKeySetClient as FITKeySetClientContract;
use BenColmer\LaravelFITValidator\Contracts\FITValidator as FITValidatorContract;
use BenColmer\LaravelFITValidator\Exceptions\ConfigurationException;
use BenColmer\LaravelFITValidator\Exceptions\ValidationException;
use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
     * The key set client.
     */
    protected FITKeySetClientContract $keySetClient;

    public function __construct(string $appKey = 'default')
    {
        $this->appId = (string) Config::get("fit.applications.{$appKey}.appId");
        if (! $this->appId) throw new ConfigurationException($appKey, 'appId');

        $this->keySetClient = App::makeWith(FITKeySetClientContract::class, [
            'appKey' => $appKey
        ]);
    }

    public function validate(Request|string $input): ?array
    {
        $jwt = is_string($input) ? $input : $this->parseToken($input);
        if (! is_string($jwt)) {
            throw new ValidationException('JWT is missing from the validation input.');
        }

        $jwks = $this->keySetClient->get();
        if (! $jwks) {
            throw new ValidationException('Failed to retrieve JWKS.');
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
            if (Config::get('app.debug', false)) {
                Log::info('FIT failed validation.');
            }

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
}
