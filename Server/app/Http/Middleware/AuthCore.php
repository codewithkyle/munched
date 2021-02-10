<?php

namespace App\Http\Middleware;

use \Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use \Firebase\JWT\JWT;

class AuthCore
{
    protected function processRequest(Request $request)
    {
        $token = $this->getTokenFromRequeset($request);

        if (empty($token)) {
            return null;
        }

        try{
            $payload = JWT::decode($token, env("JWT_SECRET"), ['HS256']);
        }catch (\Exception $e){
            return null;
        }

        if ($this->tokenIsBlacklisted($token)){
            return null;
        }

        $user = Cache::get("user-" . $payload->sub);
        if (is_null($user)){
            $user = User::where('uid', $payload->sub)->first();
            Cache::put("user-" . $user->uid, json_encode($user));
        } else {
            $user = json_decode($user);
        }

        $request->request->add([
            "user" => $user,
            "token" => $token,
            "payload" => $payload,
        ]);

        return $request;
    }

    protected function tokenIsBlacklisted(string $token): bool
    {
        $blacklist = Cache::get("blacklist", json_encode([]));
        $blacklist = json_decode($blacklist);
        $currentTime = time();
        $isBlacklisted = false;
        foreach($blacklist as $key => $jwt){
            if ($jwt->token === $token){
                $isBlacklisted = true;
            }
            if ($jwt->exp <= $currentTime){
                unset($blacklist[$key]);
            }
        }
        Cache::put("blacklist", json_encode($blacklist));
        return $isBlacklisted;
    }

    protected function returnUnverified(): JsonResponse
    {
        return response()->json([
            "success" => false,
            "data" => null,
            "error" => "Email address has not been verified."
        ], 403);
    }

    protected function getTokenFromRequeset(Request $request): string
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

    protected function returnTokenException(\Exception $e): JsonResponse
    {
        return response()->json([
            "success" => false,
            "data" => null,
            "error" => $e->getMessage()
        ]);
    }

    protected function returnUnauthorized(): JsonResponse
    {
        return response()->json([
            "success" => false,
            "data" => null,
            "error" => "You are not authorized to preform this action."
        ], 401);
    }
}
