<?php
declare(strict_types=1);

use App\Services\TikTokScraperService;
use Illuminate\Support\Facades\Config;

class TikTokScraperTest extends TestCase
{
    protected $scraperService;

    public function setUp(): void
    {
        parent::setUp();

        Config::set('tiktok.base_url', 'https://tiktok.com');
        $this->scraperService = new TikTokScraperService();
    }

    public function testISO8601ToSecondsShouldReturnValidConversion()
    {
        $this->assertEquals(30, $this->scraperService->ISO8601ToSeconds('PT30S'));
    }

    public function testISO8601ToSecondsShouldReturnZeroWithInvalidParameter()
    {
        $this->assertEquals(0, $this->scraperService->ISO8601ToSeconds('834784'));
    }

    public function testAttributes() {
        $this->assertClassHasAttribute('userDetails', TikTokScraperService::class);
        $this->assertClassHasAttribute('videoDetails', TikTokScraperService::class);
    }
}
