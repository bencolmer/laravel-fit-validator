# Laravel FIT Validator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bencolmer/laravel-fit-validator.svg?style=flat-square)](https://packagist.org/packages/bencolmer/laravel-fit-validator)

This package allows you to validate and use Atlassian [Forge Invocation Tokens](https://developer.atlassian.com/platform/forge/remote/essentials/#the-forge-invocation-token--fit-) (FITs) in Laravel.

## Installation

1. Install the package via composer:

```bash
composer require bencolmer/laravel-fit-validator
```

2. Configure `.env` values:

```dotenv
FIT_APP_ID="example:id::app/xxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxx" # The ID of your Forge application
FIT_JWKS_URL="https://forge.cdn.prod.atlassian-dev.net/.well-known/jwks.json" # The JWKS URL for your Forge application
```

3. Add the `fit` middleware to any routes that should validate Forge Invocation Tokens.

```php
Route::middleware('fit')->group(function () {
    //
});
```

## Token Usage

The `fit` middleware will validate the Forge Invocation Token and add the validated payload to the request input array.

Example:

```php
// in routes/api.php

Route::middleware('fit')->group(function () {
    Route::get('example', [ExampleController::class, 'index']);
});
```

```php
// in app/Http/Controllers/ExampleController.php

use Illuminate\Http\Request;

class JiraController extends Controller
{
    public function index(Request $request)
    {
        $fit = $request->input('fit');

        // ...
    }
}
```

## Advanced Usage

You can configure the package validate FIT tokens from multiple Forge applications:

1. Publish package configuration

```bash
php artisan vendor:publish --provider="BenColmer\LaravelFITValidator\Providers\ServiceProvider"
```

2. Add your Forge application details to the `applications` array in `config/fit.php`:

```php
// ...

'applications' => [
    // ...

    // details for your other application
    'otherApp' => [
        'appId' => (string) env('FIT_OTHER_APP_ID', ''),
        'jwksUrl' => (string) env('FIT_OTHER_JWKS_URL', ''),
    ]
],
```

3. Update the `fit` middleware to use the configuration for your new application

```php
// in routes/api.php

Route::middleware('fit')->group(function () {
    // validate FITs using the "default" application config
});

Route::middleware('fit:otherApp')->group(function () {
    // validate FITs using the "otherApp" application config
});
```

## Additional Configuration

The following options are also available by publishing the package configuration:

| Name | Default Value | Purpose |
|-------|---------|---------|
| `middlewareAlias` | `fit` | Defines the alias for the FIT validation middleware |
| `issuer` | `forge/invocation-token` | Defines the expected Forge Invocation Token issuer |
| `jwksCacheDuration` | 5 minutes | Defines the cache duration for fetched JSON Web Key Sets. Setting this to `null` will disable caching. |


## Testing

Run tests via PHPUnit:

```bash
./vendor/bin/phpunit
```

## Credits

- [Ben Colmer](https://github.com/bencolmer)

## License

Laravel FIT Validator is open-sourced software licensed under the [MIT license](LICENSE.md).
