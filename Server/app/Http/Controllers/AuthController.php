<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use \Firebase\JWT\JWT;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailConfirm;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\UserService;
use Illuminate\Support\Facades\Cache;

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
            $data = array_merge($this->generateToken($user->id), [
                "pendingEmailVerification" => $user->verified ? false : true,
            ]);
            Cache::put("user-" . $user->id, json_encode($user));
            return $this->buildSuccessResponse($data);
        } else {
            return $this->buildErrorResponse("Incorrect email or password.");
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $blacklist = Cache::get("blacklist", json_encode([]));
        $blacklist = json_decode($blacklist);
        $blacklist[] = [
            "token" => $request->token,
            "exp" => $request->payload->exp,
        ];
        Cache::put("blacklist", json_encode($blacklist));
        return $this->buildSuccessResponse();
    }

    public function refreshToken(Request $request): JsonResponse
    {
        return $this->buildSuccessResponse($this->generateToken($request->user->id));
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
        $verificationCode = str_replace("-", "", Uuid::uuid4()->toString());

        $user = User::create([
            "name" => $name,
            "email" => $email,
            "password" => $password,
            "uid" => $uid,
            "email_verification_code" => $verificationCode,
            "groups" => ["global"],
        ]);

        $encodedData = $this->encodeData(json_encode([
            "email" => $email,
            "code" => $verificationCode,
        ]));

        Mail::to($email)->send(new EmailConfirm($encodedData, $name));

        return $this->buildSuccessResponse();
    }

    public function verifyEmail(Request $request)
    {
        $data = $request->input("code");
        $data = json_decode($this->decodeData($data));
        $user = User::where('email_verification_code', $data->code)
                    ->where("email", $data->email)
                    ->first();
        if (!empty($user)){
            $user["email_verification_code"] = null;
            $user->verified = true;
            $user->save();
        }
        return redirect(rtrim(env("APP_URL"), "/") . "/verification/success");
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user;

        $encodedData = $this->encodeData(json_encode([
            "email" => $user->email,
            "code" => $user["email_verification_code"],
        ]));

        Mail::to($user->email)->send(new EmailConfirm($encodedData, $user->name));

        return $this->buildSuccessResponse();
    }

    private function generateToken(int $userId): array
    {
        $iat = time();
        $payload = [
            "iss" => env("API_URL"),
            "iat" => $iat,
            "nbf" => $iat,
            "exp" => $iat + 900,
            "sub" => $userId,
        ];
        $token = JWT::encode($payload, env("JWT_SECRET"), 'HS256');
        return [
            'token' => $token,
            'type' => 'bearer',
            'expires' => $iat + 900
        ];
    }
}
