<?php

namespace BenColmer\LaravelFITValidator\Http\Middleware;

use BenColmer\LaravelFITValidator\Contracts\FITValidator as FITValidatorContract;
use BenColmer\LaravelFITValidator\Exceptions\ValidationException;
use BenColmer\LaravelFITValidator\FITValidator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidateFIT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $appKey = 'default'): Response
    {

        $fit = $this->validate($request, $appKey);
        if (! $fit) {
            throw new HttpException(401);
        }

        // set FIT in request data
        $request->merge(['fit' => $fit]);

        return $next($request);
    }

    /**
     * Validate the FIT.
     */
    protected function validate(Request $request, string $appKey): ?array
    {
        $fit = null;

        try {
            $validator = $this->getValidator($appKey);
            $fit = $validator->validate($request, $appKey);
        } catch (ValidationException $e) {}

        return $fit;
    }

    /**
     * Get a FIT validator instance.
     */
    protected function getValidator(string $appKey): FITValidatorContract
    {
        return new FITValidator($appKey);
    }
}
