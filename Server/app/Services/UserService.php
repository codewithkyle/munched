<?php

namespace App\Services;

use App\Models\User;

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
}
