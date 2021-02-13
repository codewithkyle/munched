<?php

namespace App\Services;

use Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

// Models
use App\Models\User;
use App\Models\EmailVerification;
use App\Models\PasswordReset;

// Emails
use App\Mail\EmailConfirm;
use App\Mail\ForgottenPassword;
use App\Mail\PasswordChanged;

class UserService
{
    private $user;

    function __construct(User $user) {
        $this->user = $user;
    }

    protected $permissions = [
        "global" => [
            "profile:update",
            "meal:create",
            "meal:update",
            "meal:delete"
        ],
        "manager" => [
            "ingredients:create",
            "ingredients:update",
            "ingredients:delete",
        ]
    ];

    public function grantAdminStatus(): void
    {
        $this->user->admin = true;
        $this->save();
    }

    public function revokeAdminStatus(): void
    {
        $this->user->admin = false;
        $this->save();
    }

    public function activate(): void
    {
        $this->user->verified = true;
        $this->purgeEmailVerificationRequests();
        $this->save();
    }

    public function suspend(): void
    {
        $this->user->suspended = true;
        $this->user->admin = false;
        // Add a suspension email here
        $this->save();
    }

    public function unsuspend(): void
    {
        $this->user->suspended = false;
        $this->save();
    }

    public function addGroup(string $groupToAdd): void
    {
        if (isset($this->permissions[$groupToAdd]) && !in_array($this->user->groups, $groupToAdd)){
            $updatedGroups = [$groupToAdd];
            foreach ($this->user->groups as $group){
                $updatedGroups[] = $group;
            }
            $this->user->groups = $updatedGroups;
            $this->user->save();
        }
    }

    public function removeGroup(string $groupToRemove): void
    {
        if (in_array($groupToRemove, $this->user->groups)){
            $updatedGroups = [];
            foreach ($this->user->groups as $userGroup){
                if ($userGroup !== $groupToRemove){
                    $updatedGroups[] = $userGroup;
                }
            }
            $this->user->groups = $updatedGroups;
            $this->user->save();
        }
    }

    public function can(string $permission): bool
    {
        $allowed = false;
        foreach ($this->user->groups as $group){
            if (in_array($permission, $this->permissions[$group])){
                $allowed = true;
                break;
            }
        }
        return $allowed;
    }

    public function createEmailVerification(string $email): void
    {
        $verificationCode = Uuid::uuid4()->toString();

        $encodedData = $this->encodeData(json_encode([
            "email" => $email,
            "code" => $verificationCode,
        ]));

        $verification = EmailVerification::create([
            "emailVerificationCode" => $encodedData,
            "userId" => $this->user->id,
            "email" => $email,
        ]);

        $mail = new EmailConfirm($encodedData, $this->user->name);
        $this->sendMail($email, $mail);

        $this->user->verified = false;
        $this->save();
    }

    public function resendVerificationEmail(): void
    {
        $verificationRequest = EmailVerification::where("userId", $this->user->id)->whereNotNull("emailVerificationCode")->first();
        $mail = new EmailConfirm($verificationRequest->emailVerificationCode, $this->user->name);
        $this->sendMail($verificationRequest->email, $mail);
    }

    public function verifyEmailAddress(EmailVerification $verificationRequest): void
    {
        $this->user->email = $verificationRequest->email;
        $this->user->verified = true;
        $this->save();
        $this->purgeEmailVerificationRequests();
    }

    public function updateProfile(array $params): void
    {
        $this->user->name = $params["name"];
        $this->save();
    }

    public function createPasswordReset(): void
    {
        $verificationCode = Uuid::uuid4()->toString();
        $encodedData = $this->encodeData($verificationCode);
        $passwordResetRequeset = PasswordReset::create([
            "userId" => $this->user->id,
            "emailVerificationCode" => $encodedData,
        ]);
        $mail = new ForgottenPassword($passwordResetRequeset->emailVerificationCode, $this->user->name);
        $this->sendMail($this->user->email, $mail);
    }

    public function resetPassword(string $password): void
    {
        $this->user->password = $password;
        $this->save();
        $this->prugePasswordResetRequests();
        $mail = new PasswordChanged($this->user->name);
        $this->sendMail($this->user->email, $mail);
    }

    private function prugePasswordResetRequests(): void
    {
        $requests = PasswordReset::where("userId", $this->user->id)->whereNotNull("emailVerificationCode")->get();
        foreach ($requests as $request){
            $request->emailVerificationCode = null;
            $request->save();
        }
    }

    private function purgeEmailVerificationRequests(): void
    {
        $requests = EmailVerification::where("userId", $this->user->id)->whereNotNull("emailVerificationCode")->get();
        foreach ($requests as $request){
            $request->emailVerificationCode = null;
            $request->save();
        }
    }

    private function save()
    {
        $this->user->save();
        Cache::put("user-" . $this->user->uid, json_encode($this->user));
    }

    private function sendMail(string $email, \Illuminate\Mail\Mailable $mail): void
    {
        try{
            Mail::to($email)->send($mail);
        } catch (\Exception $e){
            Log::error($e->getMessage());
        }
    }

    private function encodeData (string $data): string
    {
        return base64_encode($data);
    }

    private function decodeData(string $encodedData): string
    {
        return base64_decode($encodedData);
    }
}
