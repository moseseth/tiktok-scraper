<?php
return [
    'base_url' => env('TIkTOK_URL', 'https://www.tiktok.com'),
    'script_user_path' => env('SCRIPT_USER_PATH', '/@:uniqueId'),
    'script_video_path' => env('SCRIPT_VIDEO_PATH', '/@:uniqueId/video/:id')
];
