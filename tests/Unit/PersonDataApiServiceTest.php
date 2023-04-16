<?php

namespace Tests\Unit;

use App\Services\PersonDataApiService;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Http;
use Tests\CreatesApplication;

class PersonDataApiServiceTest extends TestCase
{
    use CreatesApplication;

    public function testGetPersonData()
    {
        $email = 'demi@algolia.com';
        $mockResponse = [
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

        Http::fake([
            '*person*' => Http::response($mockResponse, 200),
        ]);

        $personDataApiService = new PersonDataApiService();

        $personData = $personDataApiService->getPersonData($email);

        $this->assertEquals($mockResponse, $personData);
    }
}
