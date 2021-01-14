<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Models\User;
use \Firebase\JWT\JWT;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->header("authorization");
        if (!$token){
            $token = $request->input("token");
        } else {
            $token = trim(substr($token, 7));
        }
        if (!$token) {
            return response()->json([
                "success" => false,
                "data" => null,
                "error" => "Unauthorized."
            ], 401);
        }

        try{
            $payload = JWT::decode($token, env("JWT_SECRET"), ['HS256']);
        }catch (\Exception $e){
            return response()->json([
                "success" => false,
                "data" => null,
                "error" => $e->getMessage()
            ], 500);
        }

        $user = User::where("id", $payload->sub)->first();

        $request->request->add([
            'token' => $token,
            'payload' => $payload,
            'user' => $user,
        ]);

        return $next($request);
    }
}
