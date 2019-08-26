<?php

namespace App\Jobs;

use App\models\User;
use App\models\Video;
use Illuminate\Support\Facades\DB;

class HandleDatabaseOperations extends Job
{
    private $users;
    private $videos;
    private $exclude;

    /**
     * Create a new job instance.
     *
     * @param $users
     * @param $videos
     * @param bool $exclude
     */
    public function __construct($users, $videos, $exclude = false)
    {
        $this->users = $users;
        $this->videos = $videos;
        $this->exclude = $exclude;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->createOrUpdateUsers($this->users, $this->exclude);
    }

    /**
     * @param array $users
     * @param $exclude
     */
    private function createOrUpdateUsers(array $users, bool $exclude)
    {
        DB::transaction(function () use ($users, $exclude) {
            foreach ($users as $user) {
                $user_id = ['short_name' => $user['short_name']];
                $updatedUser = User::updateOrCreate($user_id, $user);

                $videos = $user['videos'] ?? [];
                $this->createOrUpdateVideos($videos, $updatedUser->id);
            }
        }, 3);
    }


    /**
     * @param $userId
     * @param array $videos
     */
    private function createOrUpdateVideos(array $videos, $userId)
    {
        DB::transaction(function () use ($videos, $userId) {
            foreach ($videos as $video) {
                $video['user_id'] = $userId;
                $video_id = ['video_id' => $video['video_id']];
                unset($video['video_id']);
                Video::updateOrCreate($video_id, $video);
            }
        }, 3);
    }
}
