<?php

namespace App\Http\Controllers;
use App\Services\ZoomService;
use Illuminate\Http\Request;

class ZoomController extends Controller
{
    protected $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function createMeeting(Request $request)
    {
        $data = $request->all();
        $meeting = $this->zoomService->createMeeting($data);

        return response()->json($meeting);
    }

    public function listMeetings()
    {
        $meetings = $this->zoomService->listMeetings();

        return response()->json($meetings);
    }
}
