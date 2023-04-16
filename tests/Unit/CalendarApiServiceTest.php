<?php

namespace Tests\Unit;

use App\Services\CalendarApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class CalendarApiServiceTest extends TestCase
{
    use CreatesApplication;

    public function testGetEvents()
    {
        $apiKey = '7S$16U^FmxkdV!1b';
        $page = 1;
        $mockResponse = [
            'total' => 2,
            "per_page" => 10,
            "current_page" => "1",
            'data' => [
                [
                    "id" => 1,
                    "changed" => "2022-06-27 11:32:12",
                    "start" => "2022-07-01 09:30:00",
                    "end" => "2022-07-01 10:00:00",
                    "title" => "UserGems x Algolia",
                    "accepted" => [
                        "stephan@usergems.com",
                        "joss@usergems.com",
                        "demi@algolia.com",
                        "joshua@algolia.com",
                        "woojin@algolia.com"
                    ],
                    "rejected" => [
                        "aletta@algolia.com"
                    ]
                ],
                [
                    "id" => 2,
                    "changed" => "2022-06-23 12:32:12",
                    "start" => "2022-06-25 10:00:00",
                    "end" => "2022-06-25 12:00:00",
                    "title" => "UserGems x Algolia",
                    "accepted" => [
                        "stephan@usergems.com",
                        "christian@usergems.com",
                        "demi@algolia.com",
                        "joshua@algolia.com",
                        "aletta@algolia.com"
                    ],
                    "rejected" => []
                ],
            ],
        ];

        Http::fake([
            '*events*' => Http::response($mockResponse, 200),
        ]);

        $calendarApiService = new CalendarApiService();

        $events = $calendarApiService->getEvents($apiKey, $page);

        $this->assertEquals($mockResponse, $events);
    }

    public function testGetEventsForToday()
    {
        $apiKey = 'test_api_key';
        $page = 1;
        $mockResponse = [
            'total' => 1,
            "per_page" => 10,
            "current_page" => "1",
            'data' => [
                [
                    "id" => 1,
                    "changed" => "2022-06-27 11:32:12",
                    "start" => "2022-07-01 09:30:00",
                    "end" => "2022-07-01 10:00:00",
                    "title" => "UserGems x Algolia",
                    "accepted" => [
                        "stephan@usergems.com",
                        "joss@usergems.com",
                        "demi@algolia.com",
                        "joshua@algolia.com",
                        "woojin@algolia.com"
                    ],
                    "rejected" => [
                        "aletta@algolia.com"
                    ]
                ],
            ],
        ];

        Http::fake([
            '*events*' => Http::response($mockResponse, 200),
        ]);

        $calendarApiService = new CalendarApiService();

        $todayEvents = $calendarApiService->getEventsForToday($apiKey, $page);

        $expectedEvents = array_filter($mockResponse['data'], function ($event) {
            return strtotime($event['start']) >= strtotime('today') && strtotime($event['start']) < strtotime('tomorrow');
        });

        $this->assertEquals($expectedEvents, $todayEvents);
    }
}
