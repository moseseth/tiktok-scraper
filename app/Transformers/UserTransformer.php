<?php


namespace App\Transformers;


use App\models\User;
use App\models\Video;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['video'];

    public function transform(User $user)
    {
        return [
            'user_id' => '@' . $user->short_name,
            'full_name' => $user->full_name,
            'is_verified' => (boolean)$user->is_verified,
            'bio' => $user->biography,
            'thumbnail_image' => $user->avatar,
            'total' => [
                'fans' => $user->fan_count,
                'hearts' => (int) $user->heart_count,
                'followings' => $user->following_count,
                'videos' => $user->video_count
            ]
        ];
    }

    public function includeVideo(User $user)
    {
        $video = $user->videos;

        if (is_array($video)) {
            $video = Video::hydrate($video);
        }

        return $this->collection($video, new VideoTransformer(true));
    }

}
