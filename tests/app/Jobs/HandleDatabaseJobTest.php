<?php
declare(strict_types=1);

use App\Jobs\HandleDatabaseOperations;
use App\models\User;
use App\models\Video;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Faker\Factory as Faker;

class HandleDatabaseJobTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    protected $faker;
    protected $users;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->expectsJobs(HandleDatabaseOperations::class);

        $this->users = [
            'short_name' => 'realmadrid',
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

        $this->beginDatabaseTransaction();
        User::updateOrCreate(['short_name' => $this->users['short_name']], $this->users);

        $this->seeInDatabase('users', [
            'short_name' => 'realmadrid'
        ]);
    }

    public function testHandlerDispatchedFromVideoRoute()
    {
        $videos = [
            'video_id' => 6728320568954768646,
            'uid' => 'realmadrid',
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

        $this->beginDatabaseTransaction();
        $user =  User::updateOrCreate(['short_name' => $videos['uid']], $this->users);
        $videos['user_id'] = $user->id;
        Video::updateOrCreate(['video_id' => $videos['video_id']], $videos);

        $this->seeInDatabase('videos', [
            'video_id' => 6728320568954768646
        ]);
    }
}
