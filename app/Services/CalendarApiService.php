<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CalendarApiService
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.calendar_api_url');
    }

    public function getEvents($apiKey, $page = 1)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey
        ])->get($this->baseUrl . 'events?page=' . $page);

        return $response->json();
    }

    public function getEventsForToday($apiKey, $page = 1)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey
        ])->get($this->baseUrl . 'events?page=' . $page);

        $events = $response->json();

        // Check if events are empty or if all events are for yesterday or earlier, if yes return null
        if (empty($events['data']) || strtotime($events['data'][0]['start']) < strtotime('today')) {
            return [];
        }

        // Check if events on current page are for today
        $todayEvents = array_filter($events['data'], function ($event) {
            return strtotime($event['start']) >= strtotime('today') && strtotime($event['start']) < strtotime('tomorrow');
        });

        // If there are more events for today, get them on the next page
        if ($events['data'] < $events['total']) {
            $nextPage = $page + 1;
            $nextEvents = $this->getEventsForToday($apiKey, $nextPage);
            $events['data'] = array_merge($todayEvents, $nextEvents);
        } else {
            $events['data'] = $todayEvents;
        }

        return $events['data'];
    }

    public function getEventsForTodayTest($apiKey, $page = 1)
    {
        // Simulate time for testing purposes
        $currentDateTime = Carbon::create(2022, 7, 1, 6, 0, 0);
        Carbon::setTestNow($currentDateTime);
        $currentDateTime = Carbon::now();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey
        ])->get($this->baseUrl . 'events?page=' . $page);

        $events = $response->json();

        if (empty($events['data']) || strtotime($events['data'][0]['start']) < strtotime($currentDateTime->toDateTimeString())) {
            return null;
        }

        $todayEvents = array_filter($events['data'], function ($event) use ($currentDateTime) {
            return strtotime($event['start']) >= strtotime($currentDateTime->toDateTimeString());
        });

        if ($events['data'] < $events['total']) {
            $nextPage = $page + 1;
            $nextEvents = $this->getEventsForToday($apiKey, $nextPage);
            $events['data'] = array_merge($todayEvents, $nextEvents);
        } else {
            $events['data'] = $todayEvents;
        }

        return $events['data'];
    }
}
