<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Mass assignable attributes
     */
    protected $fillable = ['short_name', 'full_name', 'is_verified', 'biography', 'avatar', 'fan_count', 'heart_count',
        'following_count', 'video_count'];


    public function videos()
    {
        return $this->hasMany('App\Models\Video')->select('video_id', 'user_id');
    }
}
