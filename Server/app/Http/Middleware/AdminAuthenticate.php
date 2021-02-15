<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdminAuthenticate extends AuthCore
{
    public function handle($request, Closure $next)
    {
        $request = $this->processRequest($request);

        if (\is_null($request)) {
            return $this->returnUnauthorized();
        }

        if (!$request->user->admin) {
            return $this->returnUnauthorized();
        }

        return $next($request);
    }
}
