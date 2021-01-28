<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use \Firebase\JWT\JWT;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\UserService;
use Illuminate\Support\Facades\Cache;
use App\Models\EmailVerification;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()){
            return $this->buildValidationErrorResponse($validator, "Login form contains errors.");
        }

        $email = $request->input("email");
        $password = $request->input("password");
        $user = User::where('email', $email)->first();

        if (empty($user)){
            $error = "An account with the email " . $email . " does not exists.";
            return $this->buildErrorResponse($error);
        }

        if (Hash::check($password, $user->password)){
            $data = array_merge($this->generateToken($user->uid), [
                "pendingEmailVerification" => $user->verified ? false : true,
            ]);
            Cache::put("user-" . $user->uid, json_encode($user));
            return $this->buildSuccessResponse($data);
        } else {
            return $this->buildErrorResponse("Incorrect email or password.");
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->blacklistToken($request->token, $request->payload->exp);
        return $this->buildSuccessResponse();
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $newToken = $this->generateToken($request->user->uid);
        if ($newToken["token"] !== $request->token){
            $this->blacklistToken($request->token, $request->payload->exp);
        }
        return $this->buildSuccessResponse($newToken);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email|unique:users|max:255",
            "password" => "required|min:6|max:255",
            "name" => "required|max:255",
        ]);
        if ($validator->fails()){
            return $this->buildValidationErrorResponse($validator, "Registration form contains errors.");
        }

        $email = $request->input("email");
        $password = Hash::make($request->input("password"));
        $name = $request->input("name");

        $uid = Uuid::uuid4()->toString();
        $secret = Uuid::uuid4()->toString();

        $user = User::create([
            "name" => $name,
            "email" => $email,
            "password" => $password,
            "uid" => $uid,
            "groups" => ["global"],
            "secret" => $secret,
        ]);

        $userService = new UserService($user);
        $userService->createEmailVerification($email);

        return $this->buildSuccessResponse();
    }

    public function verifyEmail(Request $request)
    {
        $success = false;
        $data = $request->input("code");
        $verificationRequest = EmailVerification::where('emailVerificationCode', $data)->first();
        if (!empty($verificationRequest)){
            $user = User::where('id', $verificationRequest->userId)->first();
            if (!empty($user)){
                $userService = new UserService($user);
                $userService->verifyEmailAddress($verificationRequest);
                $success = true;
            }
        }
        if ($success){
            return redirect(rtrim(env("APP_URL"), "/") . "/verification/success");
        } else{
            return redirect(rtrim(env("APP_URL"), "/") . "/verification/error");
        }
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user;
        $userService = new UserService($user);
        $userService->resendVerificationEmail();
        return $this->buildSuccessResponse();
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "newPassword" => "required|min:6|max:255",
            "oldPassword" => "required|min:6|max:255",
        ]);
        if ($validator->fails()){
            return $this->buildValidationErrorResponse($validator, "Password update form contains errors.");
        }

        $user = User::where('id', $request->user->id)->first();
        if (!empty($user)){
            if (Hash::check($request->input("oldPassword"), $user->password)){
                $newPassword = Hash::make($request->input("newPassword"));
                $user->password = $newPassword;
                $user->save();
                return $this->buildSuccessResponse();
            } else {
                return $this->buildErrorResponse("Incorrect current password.");
            }
        } else {
            return $this->buildErrorResponse("Failed to find user.");
        }
    }

    private function generateToken(string $userUid): array
    {
        $iat = time();
        $exp = $iat + env("JWT_TIMEOUT");
        $payload = [
            "iss" => env("API_URL"),
            "iat" => $iat,
            "nbf" => $iat,
            "exp" => $exp,
            "sub" => $userUid,
        ];
        $token = JWT::encode($payload, env("JWT_SECRET"), 'HS256');
        return [
            'token' => $token,
            'type' => 'bearer',
            'expires' => $exp,
        ];
    }

    private function blacklistToken(string $token, int $exp): void
    {
        $blacklist = Cache::get("blacklist", json_encode([]));
        $blacklist = json_decode($blacklist);
        $blacklist[] = [
            "token" => $token,
            "exp" => $exp,
        ];
        Cache::put("blacklist", json_encode($blacklist));
    }
}
