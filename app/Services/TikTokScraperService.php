<?php

namespace App\Services;

use Campo\UserAgent;
use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

final class TikTokScraperService
{
    private $client;
    public $userDetails = array();
    public $videoDetails = array();

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('tiktok.base_url'),
            'headers' => [
                'User-Agent' => UserAgent::random()
            ]
        ]);
    }

    /**
     * @param array $userIds
     * @return array
     */
    public function extractUsers(array $userIds)
    {
        $requests = function ($userIds) {
            foreach ($userIds as $userId) {
                yield $userId => function () use ($userId) {
                    return $this->client->getAsync("/$userId");
                };
            }
        };

        $this->scrapeTikTok(config('tiktok.script_user_path'), $requests, $userIds);

        return $this->userDetails;
    }


    /**
     * @param string $userId
     * @param array $videoIds
     * @return array
     */
    public function extractVideos(string $userId, array $videoIds)
    {
        $requests = function ($videoIds) use ($userId) {
            foreach ($videoIds as $videoId) {
                if ((int)$videoId != 0) {
                    yield $videoId => function () use ($userId, $videoId) {
                        return $this->client->getAsync("/$userId/video/$videoId");
                    };
                }
            }
        };

        $this->scrapeTikTok(config('tiktok.script_video_path'), $requests, $videoIds);

        return $this->videoDetails;
    }

    /**
     * @param string $tiktokpath
     * @param Closure $requests
     * @param array $data
     */
    private function scrapeTikTok(string $tiktokpath, Closure $requests, array $data)
    {
        $pool = new Pool($this->client, $requests($data), [
            'concurrency' => 10,
            'fulfilled' => function (Response $response) use ($tiktokpath) {
                if ($response->getStatusCode() == 200) {
                    $crawler = new Crawler((string)$response->getBody());

                    $scrappedData = $crawler->filter('script')->reduce(function (Crawler $node, $i) {
                        return strpos($node->text(), 'window.__INIT_PROPS__') !== false;
                    })->text();
                    $cleanedUpData = preg_replace('/^window.__INIT_PROPS__[\s]=/', '',
                        $scrappedData);

                    $result = json_decode($cleanedUpData, true) ?? [];

                    [$userData, $videoListInPartial, $enhancedVideoData] = $this->getDataFromResult($tiktokpath,
                        $result);

                    if (!empty($userData)) {
                        $user = $this->getUser($userData);
                        $videos = $this->getUserVideos($videoListInPartial);
                        $this->userDetails[] = array_merge($user, ['videos' => $videos]);
                    }

                    if (!empty($enhancedVideoData['uniqueId']) && !empty($enhancedVideoData['itemInfos'])) {
                        $this->videoDetails[] = $this->getVideo($enhancedVideoData);
                    }
                }
            },
            'rejected' => function ($reason) {
                Log::error('Promise Rejection', ['[tiktok scraper]' => $reason]);
            }
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }


    /**
     * @param string $tiktokpath
     * @param array $result
     * @return array
     */
    function getDataFromResult(string $tiktokpath, array $result): array
    {
        $userData = null;
        $videoListInPartial = null;
        $enhancedVideoData = null;

        if (array_key_exists($tiktokpath, $result)) {
            $userData = $result[$tiktokpath]['userData'] ?? null;
            $videoListInPartial = $result[$tiktokpath]['itemList'] ?? null;
            $metaData = [
                'uniqueId' => $result[$tiktokpath]['uniqueId'] ?? null,
                'origin' => $result[$tiktokpath]['$origin'] ?? null,
                'pageUrl' => $result[$tiktokpath]['$pageUrl'] ?? null
            ];
            $videoData = $result[$tiktokpath]['videoData'] ?? [];
            $enhancedVideoData = array_merge($metaData, $videoData) ?? null;
        }
        return [$userData, $videoListInPartial, $enhancedVideoData];
    }

    /**
     * @param $videoListInPartial
     * @return array
     */
    function getUserVideos($videoListInPartial): array
    {
        $videos = array();
        if (!empty($videoListInPartial)) {
            foreach ($videoListInPartial as $video) {
                $isValidUrl = filter_var($video['url'], FILTER_VALIDATE_URL);
                $splicedVideoUrl = $isValidUrl ? preg_split('[/]', $video['url'],
                    -1, PREG_SPLIT_NO_EMPTY) : [];

                $videos[] = [
                    'video_id' => (int)$splicedVideoUrl[4] ?? null,
                    'uid' => (int)$splicedVideoUrl[2] ?? null,
                    'url' => $video['url'] ?? null,
                    'background_image' => $video['thumbnailUrl'][0] ?? null,
                    'content_url' => $video['contentUrl'] ?? null,
                    'duration_in_second' => $this->ISO8601ToSeconds($video['duration']) ?? null,
                    'description' => $video['name'] ?? null,
                    'comment_count' => $video['commentCount'] ?? 0,
                    'like_count' => $video['interactionCount'] ?? 0
                ];
            }
        }
        return $videos;
    }

    /**
     * @param $userData
     * @return array
     */
    function getUser(array $userData): array
    {
        return [
            'short_name' => $userData['uniqueId'],
            'full_name' => $userData['nickName'],
            'is_verified' => $userData['verified'],
            'biography' => $userData['signature'],
            'avatar' => $userData['coversMedium'][0],
            'following_count' => $userData['following'] ?? 0,
            'fan_count' => $userData['fans'] ?? 0,
            'heart_count' => $userData['heart'] ?? 0,
            'video_count' => $userData['video'] ?? 0
        ];
    }

    /**
     * @param $video
     * @return array
     */
    function getVideo(array $video): array
    {
        return [
            'video_id' => (int)$video['itemInfos']['id'] ?? null,
            'uid' => $video['uniqueId'] ?? null,
            'url' => $video['origin'] . $video['pageUrl'] ?? null,
            'background_image' => $video['itemInfos']['covers'][0] ?? null,
            'content_url' => $video['itemInfos']['video']['urls'][0] ?? null,
            'duration_in_second' => $video['itemInfos']['video']['videoMeta']['duration'] ?? null,
            'sound_name' => $video['musicInfos']['musicName'] ?? null,
            'description' => $video['itemInfos']['text'] ?? null,
            'comment_count' => $video['itemInfos']['commentCount'] ?? 0,
            'like_count' => $video['itemInfos']['diggCount'] ?? 0
        ];
    }


    /**
     * @param string $ISO8601
     * @return int
     */
    function ISO8601ToSeconds(string $ISO8601): int
    {
        try {
            $interval = new \DateInterval($ISO8601);
            return ($interval->d * 24 * 60 * 60) +
                ($interval->h * 60 * 60) +
                ($interval->i * 60) +
                $interval->s;
        } catch (\Exception $e) {
            Log::warning('[ISO8601ToSeconds]', ['message' => $e->getMessage()]);
        }

        return 0;
    }

}
