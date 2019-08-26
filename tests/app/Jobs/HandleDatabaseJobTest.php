<?php
declare(strict_types=1);

use App\Jobs\HandleDatabaseOperations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Faker\Factory as Faker;

class HandleDatabaseJobTest extends TestCase
{
    use DatabaseTransactions;

    protected $faker;
    protected $users;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->expectsJobs(HandleDatabaseOperations::class);

        $this->users = [
            'short_name' => $this->faker->firstName,
            'full_name' => $this->faker->name,
            'is_verified' => $this->faker->boolean,
            'biography' => $this->faker->text,
            'avatar' => $this->faker->imageUrl(),
            'following_count' => $this->faker->numberBetween(10, 300),
            'fan_count' => $this->faker->numberBetween(10, 300),
            'heart_count' => $this->faker->numberBetween(10, 300),
            'video_count' => $this->faker->numberBetween(10, 300),
            'video' => []
        ];
    }

    public function testHandlerDispatchedFromUserRoute()
    {
        dispatch(new HandleDatabaseOperations($this->users, null));
        $this->seeInDatabase('users', [
            'is_verified' => false
        ]);
    }

    public function testHandlerDispatchedFromVideoRoute()
    {
        $videos = [
            'video_id' => 6728320568954768646,
            'uid' => $this->faker->userName,
            'url' => $this->faker->url,
            'background_image' => $this->faker->imageUrl(),
            'content_url' => $this->faker->image(),
            'duration_in_second' => $this->faker->time('s'),
            'sound_name' => $this->faker->sentence,
            'description' => $this->faker->sentence,
            'comment_count' => $this->faker->randomDigit,
            'like_count' => $this->faker->randomDigit
        ];

        dispatch(new HandleDatabaseOperations($this->users, $videos, true));

        $this->seeInDatabase('videos', [
            'video_id' => 6728320568954768646
        ]);
    }
}
