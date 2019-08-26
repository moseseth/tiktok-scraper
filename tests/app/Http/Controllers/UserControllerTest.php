<?php
declare(strict_types=1);

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UserControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testDefaultRouteIsResponsive()
    {
        $this->get('/');

        $this->assertEquals(
            $this->app->version(), $this->response->getContent()
        );
    }

    public function testGetUsersProfileShouldReturnUserData()
    {
        $this->get('/api/users?id=@arsenal');

        $this->seeStatusCode(200);
        $this->seeJsonStructure(["data" => ['*' =>
            ["user_id",
                "full_name",
                "is_verified",
                "bio",
                "thumbnail_image",
                "total" => [
                    "fans",
                    "hearts",
                    "followings",
                    "videos"
                ],
                "video" => [
                    "data" => [
                        '*' => [
                            "video_id"
                        ]
                    ]
                ]]]]);
    }

    public function testGetUsersProfileShouldReturnNullWithInvalidOrNonExistingId()
    {
        $this->get('/api/users?id=111');

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            "data" => []
        ]);
    }

    public function testGetUserVideoShouldReturnEmptyOnNonExistingId()
    {
        $this->get('/api/users/@lorengray/videos?id=xxx');

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            "data" => []
        ]);
    }

    public function testGetUserVideoShouldReturnVideoData()
    {
        $this->get('/api/users/@wilczewska/videos?id=6728320568954768646');

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            "data" => ['*' => [
                'url',
                'upload_data',
                'duration',
                'sound',
                'description',
                'thumbnail_image',
                'total' => [
                    'likes',
                    'comments',
                ]
            ]]
        ]);
    }
}
