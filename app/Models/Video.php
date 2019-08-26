<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    /**
     * Mass assignable attributes
     */
    protected $fillable = ['video_id', 'user_id', 'url', 'background_image', 'content_url', 'duration_in_second',
        'description', 'sound_name', 'like_count', 'comment_count'];


    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
