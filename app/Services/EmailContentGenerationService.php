<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventParticipants;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;

class EmailContentGenerationService
{
    public function generateEmailContent(User $user, $events)
    {
        $eventsData = $this->generateJsonOfEventData($events, $user);

        $content = [
            'user' => [
                'email' => $user->email,
                'company' => $user->person->company->name ?? null,
            ],
            'events' => $eventsData,
        ];

        return json_encode($content);
    }

    private function generateJsonOfEventData($events, $user)
    {
        $jsonItems = [];

        foreach ($events as $event_id) {
            $eventDetails = Event::where('id', $event_id)->with('participants.person.company')->first();

            $joiningFromSameCompany = [];
            $participantsData = [];

            foreach ($eventDetails->participants as $participant) {
                if ($participant->person->company->name == $user->person->company->name) {
                    if ($participant->person->email != $user->email) {
                        $joiningFromSameCompany[] = [
                            'email' => $participant->person->email,
                            'first_name' => $participant->person->first_name,
                            'last_name' => $participant->person->last_name,
                            'avatar' => $participant->person->avatar,
                            'title' => $participant->person->title,
                            'linkedin_url' => $participant->person->linkedin_url,
                            'accepted' => $participant->is_attending,
                        ];
                    }
                } else {
                    $participantsData[] = [
                        'email' => $participant->person->email,
                        'first_name' => $participant->person->first_name,
                        'last_name' => $participant->person->last_name,
                        'avatar' => $participant->person->avatar,
                        'title' => $participant->person->title,
                        'linkedin_url' => $participant->person->linkedin_url,
                        'company' => [
                            'name' => $participant->person->company->name ?? null,
                            'linkedin_url' => $participant->person->company->linkedin_url ?? null,
                            'employees' => $participant->person->company->employees ?? null,
                        ],
                        'stats' => [
                            'total_events' => $this->getTotalEvents($participant->person),
                            'total_events_with' => $this->getTotalEventsWith($participant->person, $user),
                        ],
                        'accepted' => $participant->is_attending,
                    ];
                }
            }

            // Find the company that is most common in the participants list, and use that as the target company
            $targetCompany = $this->getTargetCompany($event_id);

            $jsonItems[] = [
                'title' => $eventDetails->title,
                'start' => $eventDetails->start,
                'end' => $eventDetails->end,
                'duration' => $this->humanReadableDuration($eventDetails->start, $eventDetails->end),
                'target_company' => [
                    'name' => $targetCompany->name,
                    'linkedin_url' => $targetCompany->linkedin_url,
                    'employees' => $targetCompany->employees,
                ],
                'joining_from_same_company' => $joiningFromSameCompany,
                'participants' => $participantsData,
            ];
        }

        return $jsonItems;
    }

    private function getTargetCompany($event_id)
    {
        $targetCompany = EventParticipants::select('companies.name', 'companies.linkedin_url', 'companies.employees')
            ->join('companies', 'event_participants.company_id', '=', 'companies.id')
            ->where('event_participants.event_id', $event_id)
            ->where('event_participants.is_attending', true)
            ->where('event_participants.company_id', '!=', null)
            ->groupBy('companies.name', 'companies.linkedin_url', 'companies.employees')
            ->orderByRaw('COUNT(*) DESC')
            ->first();

        return $targetCompany;
    }

    public function humanReadableDuration($start, $end)
    {
        $durationInMinutes = Carbon::parse($end)->diffInMinutes(Carbon::parse($start));
        $duration = CarbonInterval::minutes($durationInMinutes)->cascade()->forHumans(['short' => true]);
        return $duration;
    }

    private function getTotalEvents($person)
    {
        $totalEvents = EventParticipants::where('person_id', $person->id)->count();

        return $totalEvents;
    }

    private function getTotalEventsWith($person, $user)
    {
        $userCompany = $user->person?->company;

        if ($userCompany) {
            $eventsWithUserCompany = EventParticipants::select('persons.email', DB::raw('COUNT(*) as total_events'))
                ->join('events', 'event_participants.event_id', '=', 'events.id')
                ->join('persons', 'event_participants.person_id', '=', 'persons.id')
                ->whereHas('event.participants', function ($query) use ($userCompany, $person) {
                    $query->where('company_id', $userCompany->id)->where('persons.email', '!=', $person->email);
                })
                ->where('event_participants.created_at', '<', Carbon::now()) // Only count events that have happened
                ->groupBy('persons.email')
                ->get();

            return $eventsWithUserCompany->toArray();
        }

        return [
            'email' => null,
            'total_events' => 0,
        ];
    }
}
