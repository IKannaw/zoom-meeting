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
        $response = $this->client->post("https://api.zoom.us/v2/users/me/meetings", [
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

    public function addUser(Request $request)
    {
        $headerConfig = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ],
        ];

        try {
            $response = Http::post("https://api.zoom.us/v2/users", $request->all(), $headerConfig);
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error creating user'], 500);
        }
    }

    public function getUsers()
    {
        $client = new Client();
        $accessToken = $this->getAccessToken();

        try {
            $response = $client->request('GET', 'https://api.zoom.us/v2/users', [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ]
            ]);

            return response()->json(json_decode($response->getBody(), true), 200);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Error fetching meetings: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $body = $response->getBody()->getContents();
                Log::error('Response Status: ' . $statusCode);
                Log::error('Response Body: ' . $body);
                return response()->json(['error' => 'Failed to fetch meetings', 'details' => json_decode($body, true)], $statusCode);
            } else {
                return response()->json(['error' => 'Failed to fetch meetings', 'details' => $e->getMessage()], 500);
            }
        }
    }
}
