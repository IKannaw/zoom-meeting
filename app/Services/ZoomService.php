<?php

namespace App\Services;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ZoomService
{
    protected $client;
    protected $accessToken;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.zoom.us/v2/',
            'timeout'  => 10.0,
        ]);

        $this->accessToken = $this->getAccessToken();
    }

    protected function getAccessToken()
    {
        $client = new Client();
        $response = $client->post('https://zoom.us/oauth/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(env('ZOOM_CLIENT_ID') . ':' . env('ZOOM_CLIENT_SECRET')),
            ],
            'form_params' => [
                'grant_type' => 'account_credentials',
                'account_id' => env('ZOOM_ACCOUNT_ID'),
            ],
            'verify' => false,
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['access_token'];
    }

    public function createMeeting(Request $request)
    {
        $response = $this->client->post('https://api.zoom.us/v2/users/me/meetings', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'topic' => $request->input('topic'),
                'type' => 1,
                'start_time' => $request->input('start_time'),
                'duration' => $request->input('duration'),
                'timezone' => $request->input('timezone'),
                'settings' => [
                    'join_before_host' => true,
                    'host_video' => true,
                    'participant_video' => true,
                    'mute_upon_entry' => false,
                    'waiting_room' => false,
                ],
            ],
        ]);

        return response()->json(json_decode($response->getBody()->getContents()), $response->getStatusCode());
    }

    public function listMeetings()
    {
        $response = $this->client->get('users/me/meetings', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
