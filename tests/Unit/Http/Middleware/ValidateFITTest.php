<?php

namespace BenColmer\LaravelFITValidator\Tests\Unit\Http\Middleware;

use BenColmer\LaravelFITValidator\Contracts\FITValidator as FITValidatorContract;
use BenColmer\LaravelFITValidator\FITValidator;
use BenColmer\LaravelFITValidator\Http\Middleware\ValidateFIT;
use BenColmer\LaravelFITValidator\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidateFITTest extends TestCase
{
    private ValidateFIT $middleware;

    public function setUp(): void
    {
        parent::setUp();

        Config::set([
            'fit.applications.default.appId' => 'test',
            'fit.applications.default.jwksUrl' => 'https://forge.cdn.prod.atlassian-dev.net/.well-known/jwks.json',
        ]);

        $this->middleware = new ValidateFIT();
    }

    #[Test]
    public function it_fails_request_missing_fit(): void
    {
        $this->expectException(HttpException::class);

        $this->middleware->handle(
            Request::create('test'),
            fn() => new Response(),
            'default'
        );
    }

    #[Test]
    public function it_fails_request_with_invalid_fit(): void
    {
        $this->expectException(HttpException::class);

        $request = Request::create('test');
        $request->headers->set('authorization', 'Bearer ' . fake()->sha256());

        $this->middleware->handle($request, fn() => new Response(), 'default');
    }

    #[Test]
    public function it_passes_request_with_valid_fit(): void
    {
        $this->app->bind(FITValidatorContract::class, function () {
            $mock = $this->mock(FITValidator::class);

            $mock->shouldReceive('validate')
                ->once()
                ->andReturn([
                    'foo' => 'bar'
                ]);

            return $mock;
        });

        $result = $this->middleware->handle(
            Request::create('test'),
            fn() => new Response(),
            'default'
        );

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }
}
