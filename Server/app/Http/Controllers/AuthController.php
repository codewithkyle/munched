<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use \Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailConfirm;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                "success" => false,
                "data" => $validator->errors(),
                "error" => "Login form contains errors."
            ]);
        }

        $email = $request->input("email");
        $password = $request->input("password");
        $user = User::where('email', $email)->first();

        if (Hash::check($password, $user->password)){
            return $this->generateToken($user->id);
        } else {
            return response()->json([
                "success" => false,
                "data" => null,
                "error" => "Incorrect email or password."
            ]);
        }
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user;

        return response()->json([
            "success" => true,
            "data" => $user,
            "error" => null,
        ]);
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json([
            "success" => true,
            "data" => null,
            "error" => null,
        ]);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email|unique:users|max:255",
            "password" => "required|min:6|max:255",
            "fullName" => "required|max:255",
        ]);
        if ($validator->fails()){
            return response()->json([
                "success" => false,
                "data" => $validator->errors(),
                "error" => "Registration form contains errors."
            ]);
        }

        $email = $request->input("email");
        $password = Hash::make($request->input("password"));
        $fullName = $request->input("fullName");

        $uid = Uuid::uuid4()->toString();
        $verificationCode = str_replace("-", "", Uuid::uuid4()->toString());
        $user = User::create([
            "full_name" => $fullName,
            "email" => $email,
            "password" => $password,
            "uid" => $uid,
            "email_verification_code" => $verificationCode,
        ]);

        Mail::to($email)->send(new EmailConfirm($verificationCode, $fullName));

        return response()->json([
            "success" => true,
            "data" => null,
            "error" => null,
        ]);
    }

    public function verify(Request $request)
    {
        $code = $request->input("code");
        $user = User::where('email_verification_code', $code)->first();
        if (!empty($user)){
            $user["email_verification_code"] = null;
            $user->verified = true;
            $user->save();
        }
        return redirect(rtrim(env("APP_URL"), "/") . "/verified");
    }

    protected function generateToken(int $userId): JsonResponse
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
        return response()->json([
            "success" => true,
            "data" => [
                'token' => $token,
                'type' => 'bearer',
                'expires' => $iat + 900
            ],
            "error" => null,
        ]);
    }
}
