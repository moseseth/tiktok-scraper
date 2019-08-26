<?php


namespace App\Transformers;

use App\models\Video;
use League\Fractal\TransformerAbstract;

class VideoTransformer extends TransformerAbstract
{
    protected $shorten;

    public function __construct(bool $shorten = false)
    {
        $this->shorten = $shorten;
    }

    public function transform(Video $video)
    {
        if ($this->shorten) {
            return [
                'video_id' => $video->video_id
            ];
        }

        return [
            'url' => $video->url,
            'upload_data' => $video->content_url,
            'duration' => $video->duration_in_second,
            'sound' => $video->sound_name,
            'description' => $video->description,
            'thumbnail_image' => $video->background_image,
            'total' => [
                'likes' => $video->like_count,
                'comments' => $video->comment_count,
            ]
        ];
    }
}
