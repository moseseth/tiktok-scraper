<?php

namespace App\Http\Controllers;

use App\Jobs\HandleDatabaseOperations;
use App\models\User;
use App\models\Video;
use App\Transformers\UserTransformer;
use App\Transformers\VideoTransformer;
use Illuminate\Http\Request;

/**
 * @package App\Http\Controllers
 */
class UserController extends Controller
{

    /**
     * @param Request $request
     * @return array
     */
    public function getUsersProfile(Request $request): array
    {
        $userIds = array_filter(explode(',', $request->query('id')));
        $userData = $this->tikTokScraperService->extractUsers($userIds);

        dispatch(new HandleDatabaseOperations($userData, null));

        if (is_array($userData)) {
            $userData = User::hydrate($userData);
        }

        return $this->collection($userData, new UserTransformer, 'users');
    }

    /**
     * @param Request $request
     * @param $id
     * @return array
     */
    public function getUserVideos(Request $request, string $id): array
    {
        $userData = $this->tikTokScraperService->extractUsers([$id]);

        $videoIds = array_filter(explode(',', $request->query('id')));
        $videoData = $this->tikTokScraperService->extractVideos($id, $videoIds);

        dispatch(new HandleDatabaseOperations($userData, $videoData, true));

        if (is_array($videoData)) {
            $videoData = Video::hydrate($videoData);
        }

        return $this->collection($videoData, new VideoTransformer, 'videos');
    }
}
