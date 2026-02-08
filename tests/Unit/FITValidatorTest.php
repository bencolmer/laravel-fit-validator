<?php

namespace BenColmer\LaravelFITValidator\Tests\Unit;

use BenColmer\LaravelFITValidator\Contracts\FITKeySetClient as FITKeySetClientContract;
use BenColmer\LaravelFITValidator\Exceptions\ConfigurationException;
use BenColmer\LaravelFITValidator\FITKeySetClient;
use BenColmer\LaravelFITValidator\FITValidator;
use BenColmer\LaravelFITValidator\Tests\TestCase;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class FITValidatorTest extends TestCase
{
    #[Test]
    public function it_throws_exception_on_missing_config(): void
    {
        $this->expectException(ConfigurationException::class);

        $client = new FITValidator();
        $client->validate(new Request());
    }

    #[Test]
    #[DataProvider('provideConfigurationKeys')]
    public function it_passes_valid_jwt(string $appKey): void
    {
        list('jwt' => $jwt, 'payload' => $payload) = $this->mockJwt($appKey);

        $request = Request::create('test');
        $request->headers->set('authorization', 'Bearer ' . $jwt);

        $client = new FITValidator($appKey);
        $result = $client->validate($request);

        $this->assertEqualsCanonicalizing($payload, $result);
    }

    public static function provideConfigurationKeys(): array
    {
        return [
            'default configuration' => [
                'appKey' => 'default',
            ],
            'alternative configuration' => [
                'appKey' => 'altApp',
            ],
        ];
    }

    /**
     * Create a mock JWT for testing.
     */
    private function mockJwt(string $appKey): array
    {
        $issuer = 'forge/invocation-token';
        $appId = fake()->bothify('app/####****-#*#*-#*#*-#*#*-#*#*#*#*#*#*');
        Config::set([
            "fit.applications.{$appKey}.appId" => $appId,
            'fit.issuer' => $issuer,
        ]);

        $key = $this->generateTestKeyPair();
        $kid = fake()->bothify('test/token/**-####****-##**-#*#*-#*#*-####****####');

        $this->app->bind(FITKeySetClientContract::class, function () use ($kid, $key) {
            $mock = $this->mock(FITKeySetClient::class);

            $mock->shouldReceive('get')
                ->once()
                ->andReturn([
                    'keys' => [
                        [
                            'kty' => 'RSA',
                            'kid' => $kid,
                            'use' => 'sig',
                            'alg' => 'RS256',
                            'n' => JWT::urlsafeB64Encode($key['details']['rsa']['n']),
                            'e' => JWT::urlsafeB64Encode($key['details']['rsa']['e']),
                        ],
                    ],
                ]);

            return $mock;
        });

        $payload = [
            'iss' => $issuer,
            'aud' => $appId,
            'iat' => time(),
            'exp' => time() + 300,
            'foo' => 'bar',
        ];

        return [
            'jwt' => JWT::encode($payload, $key['privatePem'], 'RS256', $kid),
            'payload' => $payload,
        ];
    }

    private function generateTestKeyPair(): array
    {
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        openssl_pkey_export($key, $privatePem);

        return [
            'details' => openssl_pkey_get_details($key),
            'privatePem' => $privatePem,
        ];
    }
}
