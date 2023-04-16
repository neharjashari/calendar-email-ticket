<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PersonDataApiService
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('app.calendar_api_url');
        $this->apiKey = config('app.person_data_api_key');
    }

    public function getPersonData($email)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey
        ])->get($this->baseUrl . 'person/' . $email);

        return $response->json();
    }
}
