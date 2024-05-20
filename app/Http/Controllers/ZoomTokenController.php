<?php

namespace App\Http\Controllers;
use App\Services\ZoomService;
use Illuminate\Http\Request;

class ZoomTokenController extends Controller
{
    protected $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function fetchToken()
    {
        $accessToken = $this->zoomService->getAccessToken();
        return response()->json(['access_token' => $accessToken]);
    }
}
