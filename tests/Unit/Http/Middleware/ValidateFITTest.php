<?php

namespace BenColmer\LaravelFITValidator\Tests\Unit\Http\Middleware;

use BenColmer\LaravelFITValidator\Http\Middleware\ValidateFIT;
use BenColmer\LaravelFITValidator\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidateFITTest extends TestCase
{
    #[Test]
    public function invalid_fit_fails_validation(): void
    {
        $this->expectException(HttpException::class);

        Config::set([
            'fit.applications.default.appId' => 'invalid',
            'fit.applications.default.jwksUrl' => 'https://forge.cdn.prod.atlassian-dev.net/.well-known/jwks.json',
        ]);

        $middleware = new ValidateFIT();

        $request = Request::create('test');
        $middleware->handle($request, fn() => true, 'default');
    }
}
