<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Models\User;
use \Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class UserAuthenticate extends AuthCore
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $request = $this->processRequest($request);

        if (\is_null($request)){
            return $this->returnUnauthorized();
        }

        // Remove if users are allowed to use the applcation without a verified status
        if (!$request->user->verified && !str_contains($request->url(), "resend-verification-email")){
            return $this->returnUnverified();
        }

        if ($request->user->suspended){
            return $this->returnUnauthorized("Your account has been suspended.");
        }

        return $next($request);
    }
}
