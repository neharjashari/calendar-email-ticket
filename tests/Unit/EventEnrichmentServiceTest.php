<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\User;
use App\Services\CalendarApiService;
use App\Services\EventEnrichmentService;
use App\Services\PersonDataApiService;
use Tests\TestCase;

class EventEnrichmentServiceTest extends TestCase
{
    private $calendarApi;
    private $personDataApi;
    private $eventEnrichmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarApi = $this->createMock(CalendarApiService::class);
        $this->personDataApi = $this->createMock(PersonDataApiService::class);
        $this->eventEnrichmentService = new EventEnrichmentService($this->calendarApi, $this->personDataApi);
    }

    public function testEnrichEventsForUser()
    {
        $user = User::factory()->create([
            'api_key' => 'test-api-key',
        ]);

        $mockEvent = [
            "id" => 1,
            "changed" => "2022-06-27 11:32:12",
            "start" => "2022-07-01 09:30:00",
            "end" => "2022-07-01 10:00:00",
            "title" => "UserGems x Algolia",
            "accepted" => [
                "demi@algolia.com",
                "stephan@usergems.com",
                "joss@usergems.com",
                "joshua@algolia.com",
                "woojin@algolia.com"
            ],
            "rejected" => [
                "aletta@algolia.com"
            ]
        ];

        $mockPersonData = [
            "first_name" => "Demi",
            "last_name" => "Malnar",
            "avatar" => "https://media-exp1.licdn.com/dms/image/C4D03AQHPUFYhbLcAqw/profile-displayphoto-shrink_200_200/0/1516239694635?e=1664409600&v=beta&t=xmiukyVVGR6edLzuNnkBjA0vzfvEg-COOCmcKIjDcGk",
            "title" => "GTM Chief of Staff",
            "linkedin_url" => "https://www.linkedin.com/in/demimalnar",
            "company" => [
                "name" => "Algolia",
                "linkedin_url" => "https://www.linkedin.com/company/algolia",
                "employees" => 700
            ]
        ];

        $this->calendarApi->expects($this->once())
            ->method('getEventsForToday')
            ->with($user->api_key)
            ->willReturn([$mockEvent]);


        $this->personDataApi
            ->method('getPersonData')
            ->willReturn($mockPersonData);

        $enrichedEvents = $this->eventEnrichmentService->enrichEventsForUser($user);

        $this->assertNotEmpty($enrichedEvents);
    }
}
