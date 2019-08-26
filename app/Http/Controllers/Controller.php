<?php

namespace App\Http\Controllers;

use App\Http\Response\FractalResponse;
use App\Services\TikTokScraperService;
use Laravel\Lumen\Routing\Controller as BaseController;
use League\Fractal\TransformerAbstract;

class Controller extends BaseController
{

    private $fractal;
    protected $tikTokScraperService;

    /**
     * Controller constructor.
     * @param FractalResponse $fractal
     * @param TikTokScraperService $tikTokScraperService
     */
    public function __construct(FractalResponse $fractal, TikTokScraperService $tikTokScraperService)
    {
        $this->fractal = $fractal;
        $this->tikTokScraperService = $tikTokScraperService;
    }

    /**
     * @param $data
     * @param TransformerAbstract $transformer
     * @param null $resourceKey
     * @return array
     */
    public function item($data, TransformerAbstract $transformer, $resourceKey = null): array
    {
        return $this->fractal->item($data, $transformer, $resourceKey);
    }

    /**
     * @param $data
     * @param TransformerAbstract $transformer
     * @param null $resourceKey
     * @return array
     */
    public function collection($data, TransformerAbstract $transformer, $resourceKey = null): array
    {
        return $this->fractal->collection($data, $transformer, $resourceKey);
    }

}
