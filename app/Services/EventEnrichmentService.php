<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Event;
use App\Models\EventParticipants;
use App\Models\Person;
use App\Models\User;
use App\Services\CalendarApiService;
use App\Services\PersonDataApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EventEnrichmentService
{
    private $calendarApi;
    private $personDataApi;

    public function __construct(CalendarApiService $calendarApi, PersonDataApiService $personDataApi)
    {
        $this->calendarApi = $calendarApi;
        $this->personDataApi = $personDataApi;
    }

    public function enrichEventsForUser(User $user, $test_mode = false)
    {
        if ($test_mode) {
            $events = $this->calendarApi->getEventsForTodayTest($user->api_key);
        } else {
            $events = $this->calendarApi->getEventsForToday($user->api_key);
        }

        if (empty($events)) {
            return null;
        }

        $eventsSaved = [];

        foreach ($events as $event) {
            $eventsSaved[] = DB::transaction(function () use ($user, $event) {
                $eventModel = $this->findOrCreateEvent($event, $user);

                foreach ($event['accepted'] as $email) {
                    $person = $this->findOrCreatePerson($email, $user);

                    $this->associatePersonWithEvent($person, $eventModel, $user, true);
                }

                foreach ($event['rejected'] as $email) {
                    $person = $this->findOrCreatePerson($email, $user);

                    $this->associatePersonWithEvent($person, $eventModel, $user, false);
                }

                return $eventModel->id;
            });
        }

        return $eventsSaved;
    }

    private function findOrCreateEvent($event, $user)
    {
        $eventModel = Event::where('event_id', $event['id'])->first();

        if (!$eventModel) {
            $eventModel = new Event();
            $eventModel->user_id = $user->id;
            $eventModel->event_id = $event['id'];
            $eventModel->title = $event['title'];
            $eventModel->changed = $event['changed'];
            $eventModel->start = $event['start'];
            $eventModel->end = $event['end'];
            $eventModel->save();
        }

        return $eventModel;
    }

    private function findOrCreatePerson($email, $user)
    {
        $person = Person::where('email', $email)->first();

        if (!$person) {
            $participantData = $this->personDataApi->getPersonData($email);

            $person = $this->addPersonDataToDB($user, $email, $participantData);
        }

        // Check if 30 days have passed since last update, if so, update the data
        if ($person->last_updated && Carbon::parse($person->last_updated)->diffInDays(now()) > 30) {
            $participantData = $this->personDataApi->getPersonData($email);

            if (!empty($participantData)) {
                $person = $this->updatePersonData($person, $participantData);
            }
        }

        return $person;
    }

    private function addPersonDataToDB($user, $email, $participantData)
    {
        $person = new Person();

        $person->user_id = $user->email == $email ? $user->id : null;
        $person->email = $email;

        if (!empty($participantData)) {
            $person->first_name = $participantData['first_name'];
            $person->last_name = $participantData['last_name'];
            $person->avatar = $participantData['avatar'];
            $person->title = $participantData['title'];
            $person->linkedin_url = $participantData['linkedin_url'];

            if (isset($participantData['company']['name'])) {
                $company = Company::where('name', $participantData['company']['name'])->first();
                if ($company) {
                    $person->company_id = $company->id;
                } else {
                    $company = new Company();
                    $company->name = $participantData['company']['name'];
                    $company->linkedin_url = $participantData['company']['linkedin_url'];
                    $company->employees = $participantData['company']['employees'];
                    $company->save();
                    $person->company_id = $company->id;
                }
            }
        }

        $person->last_updated = now();
        $person->save();

        return $person;
    }

    private function updatePersonData($person, $participantData)
    {
        $person->first_name = $participantData['first_name'] ?? null;
        $person->last_name = $participantData['last_name'];
        $person->avatar = $participantData['avatar'];
        $person->title = $participantData['title'];
        $person->linkedin_url = $participantData['linkedin_url'];

        if (isset($participantData['company']['name'])) {
            $company = Company::where('name', $participantData['company']['name'])->first();
            if ($company) {
                $person->company_id = $company->id;
            } else {
                $company = new Company();
                $company->name = $participantData['company']['name'];
                $company->linkedin_url = $participantData['company']['linkedin_url'];
                $company->employees = $participantData['company']['employees'];
                $company->save();
                $person->company_id = $company->id;
            }
        }

        $person->last_updated = now();
        $person->save();

        return $person;
    }

    private function associatePersonWithEvent($person, $eventModel, $user, $is_attending)
    {
        $eventParticipant = EventParticipants::where('event_id', $eventModel->id)
            ->where('person_id', $person->id)
            ->first();

        if ($eventParticipant) {
            $eventParticipant->is_attending = $is_attending;
            $eventParticipant->save();

            return;
        }

        $eventParticipant = new EventParticipants([
            'event_id' => $eventModel->id,
            'person_id' => $person->id,
            'company_id' => $person->company_id,
            'is_attending' => $is_attending,
        ]);

        $eventParticipant->save();
    }
}
