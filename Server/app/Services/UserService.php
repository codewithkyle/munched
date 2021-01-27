<?php

namespace App\Services;

use Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

// Models
use App\Models\User;
use App\Models\EmailVerification;

// Emails
use App\Mail\EmailConfirm;

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
        Cache::put("user-" . $this->user->id, json_encode($this->user));
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
