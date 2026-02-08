<?php

namespace BenColmer\LaravelFITValidator\Tests\Unit;

use BenColmer\LaravelFITValidator\Exceptions\ConfigurationException;
use BenColmer\LaravelFITValidator\FITKeySetClient;
use BenColmer\LaravelFITValidator\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class FITKeySetClientTest extends TestCase
{
    #[Test]
    public function it_throws_exception_on_missing_config(): void
    {
        $this->expectException(ConfigurationException::class);

        $client = new FITKeySetClient();
        $client->get();
    }

    #[Test]
    #[DataProvider('provideConfigurationKeys')]
    public function it_fetches_configured_jwks(string $appKey): void
    {
        $this->setConfig($appKey);

        $client = new FITKeySetClient($appKey);
        $this->assertIsArray($client->get());
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

    private function setConfig(string $appKey): void
    {
        Config::set([
            "fit.applications.{$appKey}.appId" => 'test',
            "fit.applications.{$appKey}.jwksUrl" => 'https://forge.cdn.prod.atlassian-dev.net/.well-known/jwks.json',
        ]);
    }
}
