<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['email' => 'stephan@usergems.com', 'api_key' => '7S$16U^FmxkdV!1b'],
            ['email' => 'christian@usergems.com', 'api_key' => 'Ay@T3ZwF3YN^fZ@M'],
            ['email' => 'joss@usergems.com', 'api_key' => 'PK7UBPVeG%3pP9%B'],
            ['email' => 'blaise@usergems.com', 'api_key' => 'c0R*4iQK21McwLww'],
        ];

        $company = \App\Models\Company::factory()->create([
            'name' => 'UserGems',
            'linkedin_url' => 'https://www.linkedin.com/company/usergems',
            'employees' => 50
        ]);

        foreach ($users as $userData) {
            \App\Models\User::factory()->create($userData);

            \App\Models\Person::factory()->create([
                'user_id' => \App\Models\User::where('email', $userData['email'])->first()->id,
                'company_id' => $company->id,
                'first_name' => null,
                'last_name' => null,
                'email' => $userData['email'],
                'title' => null,
                'linkedin_url' => null,
                'avatar' => null,
            ]);
        }
    }
}
