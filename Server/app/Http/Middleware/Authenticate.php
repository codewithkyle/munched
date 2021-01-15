<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Models\User;
use \Firebase\JWT\JWT;
use \Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Authenticate
{
    /** @var Auth */
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next, $guard = null)
    {
        $token = $this->getTokenFromRequeset($request);

        if (empty($token)) {
            return $this->returnUnauthorized();
        }

        try{
            $payload = JWT::decode($token, env("JWT_SECRET"), ['HS256']);
        }catch (\Exception $e){
            return $this->returnException($e);
        }

        $user = User::where("id", $payload->sub)->first();

        // Remove if users are allowed to use the applcation without a verified status
        if (!$user->verified && !str_contains($request->url(), "resend-verification-email")){
            return $this->returnUnverified();
        }

        if ($user->suspended){
            return $this->returnUnauthorized();
        }

        $request->request->add([
            'token' => $token,
            'payload' => $payload,
            'user' => $user,
        ]);

        return $next($request);
    }

    private function returnUnverified(): JsonResponse
    {
        return response()->json([
            "success" => false,
            "data" => null,
            "error" => "Email address has not been verified."
        ], 403);
    }

    private function getTokenFromRequeset(Request $request): string
    {
        $token = $request->header("authorization");
        if (!$token){
            $token = $request->input("token");
        } else if (str_contains($token, "bearer")) {
            $token = trim(substr($token, 7));
        } else {
            $token = null;
        }
        return $token;
    }

    private function returnException(\Exception $e): JsonResponse
    {
        return response()->json([
            "success" => false,
            "data" => null,
            "error" => $e->getMessage()
        ], 500);
    }

    private function returnUnauthorized(): JsonResponse
    {
        return response()->json([
            "success" => false,
            "data" => null,
            "error" => "Unauthorized."
        ], 401);
    }
}
