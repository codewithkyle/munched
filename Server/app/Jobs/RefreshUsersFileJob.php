<?php

namespace App\Jobs;

use Ramsey\Uuid\Uuid;
use Log;

use App\Models\User;

class RefreshUsersFileJob extends Job
{
    private $uid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->uid = Uuid::uuid4()->toString();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $finalPath = storage_path('ndjson/users.ndjson');
        $tempPath = storage_path('ndjson/' . $this->uid .'.tmp');
        file_put_contents($tempPath, "");
        $users = User::chunk(200, function ($users){
            $tempPath = storage_path('ndjson/' . $this->uid .'.tmp');
            foreach ($users as $user)
            {
                $line = json_encode($user) . "\n";
                file_put_contents($tempPath, $line , FILE_APPEND);
            }
        });
        rename($tempPath, $finalPath);
    }
}
