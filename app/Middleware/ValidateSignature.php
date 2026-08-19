<?php

namespace App\Middleware;

use Illuminate\Routing\Middleware\ValidateSignature as Middleware;

class ValidateSignature extends Middleware
{
    /**
     * The names of the query string parameters that should be ignored.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Determine if the middleware should skip validation for the given request.
     */
    protected function shouldPassThrough($request): bool
    {
        return false;
    }
}
