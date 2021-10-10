<?php

use Spatie\CalendarLinks\Link;
use Spatie\CalendarLinks\Generators\Ics;

$app->Post('appointment/create', function (string $subject, array $durations) {
    if (strlen($subject) == 0) {
        Tilex::Error('Bitte einen Betreff angeben.');
    }

    $appointment = Appointment::New($_SESSION['userid'], $subject);

    foreach ($durations as $duration)
        $appointment->AddPart($duration);

    $appointment->Insert();
    Tilex::Return($appointment);
});

$app->Get('appointment', function (string $token) {
    if ($token == null || strlen($token) < 1)
        Tilex::Error("Kein oder fehlerhafter Token übergeben");

    Tilex::Return(Appointment::Get($token));
});


$app->Get('appointment/calendar', function (string $token) {

    if ($token == null || strlen($token) < 1)
        Tilex::Error("Kein oder fehlerhafter Token übergeben");

    $appointment = Appointment::Get($token);

    if ($appointment == null)
        Tilex::Error("Ungültiger Token übergeben");

    $links = array();
    foreach ($appointment->parts as $part) {
        $link = Link::create('Mein Stichtag', $part->fromDate, $part->toDate)
            ->description('Vorpflege nicht vergessen ;)')
            ->address('Schönbuchstraße 19, 70565 Stuttgart');

        $links[] = $link;

        $part->urlGoogle = $link->google();
        $part->urlOutlook = $link->webOutlook();
        $part->urlYahoo = $link->yahoo();
    }

    $appointment->url = (new Ics())->generateMultiple($links);

    Tilex::Return($appointment);
});

$app->Get('appointment/days/{year}/{month}', function (string $token, int $year, int $month) {
    if ($token == null || strlen($token) < 1)
        Tilex::Error("Kein oder fehlerhafter Token übergeben");

    $appointment = Appointment::Get($token);

    if ($appointment == null)
        Tilex::Error("Ungültiger Token übergeben");

    $start = DateTime::createFromFormat('Y.n.j G-i-s', $year . '.' . $month . '.1 0-00-00');
    $end = (clone $start)->add(new DateInterval('P1M'));

    $events = getEvents($appointment->user_id, $start, $end);

    $minDuration = min(array_map(function (AppointmentPart $x) {
        return $x->duration;
    }, $appointment->parts));

    $minDuration = new DateInterval('PT' . $minDuration . 'H');

    $startTime = new DateInterval('PT10H0M');
    $endTime = new DateInterval('PT19H0M');
    $duration = (new DateTime())->add($startTime)->diff((new DateTime())->add($endTime));

    $daysOff = array(0);

    $days = array();

    $date = (clone $start)->add($startTime);
    $dateDay = clone $start;
    $dateEnd = (clone $start)->add($endTime);

    $oneDay = new DateInterval('P1D');

    $minDay = new DateTime('tomorrow +1 day');

    if (empty($events)) {
        do {
            $day = Date::New(clone $dateDay);
            $days[] = $day;
            if (in_array($dateDay->format('w'), $daysOff) || $minDay > $dateDay)
                $day->availability = Availability::Blocked;
            else {
                $day->AddPart($startTime, $duration, Availability::Available);
            }

            $dateDay->add($oneDay);
        } while ($dateDay->format('n') == $month);
    } else {
        $dateDay->sub($oneDay);
        do {
            $dateDay->add($oneDay);

            if ($dateDay->format('n') != $month)
                break;

            $day = Date::New(clone $dateDay);
            $days[] = $day;

            $isDayOff = in_array($dateDay->format('w'), $daysOff) || $minDay > $dateDay;
            if ($isDayOff)
                $day->availability = Availability::Blocked;
        } while ($isDayOff);

        if ($dateDay->format('n') == $month) {
            $date = (clone $dateDay)->add($startTime);
            $dateEnd = (clone $dateDay)->add($endTime);

            //print "Upcoming events:\n";
            foreach ($events as $event) {
                //handle pre event
                while ($event->start > $date) {
                    if ($dateEnd <= $event->start) {
                        $day->AddPart($dateDay->diff($date), $date->diff($dateEnd), Availability::Available);
                        $date = $dateEnd;

                        //next day
                        do {
                            $dateDay->add($oneDay);
                            $day = Date::New(clone $dateDay);
                            $days[] = $day;

                            $isDayOff = in_array($dateDay->format('w'), $daysOff) || $minDay > $dateDay;
                            if ($isDayOff)
                                $day->availability = Availability::Blocked;
                        } while ($isDayOff);

                        $date = (clone $dateDay)->add($startTime);
                        $dateEnd = (clone $dateDay)->add($endTime);
                    } else {
                        $day->AddPart($dateDay->diff($date), $date->diff($event->start), Availability::Available);
                        $date = $event->start;
                    }
                }
                //handle while event
                while ($event->end > $date) {
                    if ($dateEnd <= $event->end) {
                        $day->AddPart($dateDay->diff($date), $date->diff($dateEnd), Availability::Full);
                        $date = clone $dateEnd;

                        //next day
                        do {
                            $dateDay->add($oneDay);
                            $day = Date::New(clone $dateDay);
                            $days[] = $day;

                            $isDayOff = in_array($dateDay->format('w'), $daysOff) || $minDay > $dateDay;
                            if ($isDayOff)
                                $day->availability = Availability::Blocked;
                        } while ($isDayOff);

                        $date = (clone $dateDay)->add($startTime);
                        $dateEnd = (clone $dateDay)->add($endTime);
                    } else {
                        $day->AddPart($dateDay->diff($date), $date->diff($event->end), Availability::Full);
                        $date = $event->end;
                    }
                }
            }
            //finish of day
            if ($dateDay < $dateEnd) {
                $day->AddPart($dateDay->diff($date), $date->diff($dateEnd), Availability::Available);
                $date = $dateEnd;
                $dateDay->add($oneDay);
            }
            //finish of free days
            while ($dateDay->format('n') == $month) {
                $day = Date::New(clone $dateDay);
                $days[] = $day;
                if (in_array($dateDay->format('w'), $daysOff) || $minDay > $dateDay)
                    $day->availability = Availability::Blocked;
                else {
                    $day->AddPart($startTime, $duration, Availability::Available);
                }

                $dateDay->add($oneDay);
            };
        }
    }

    global $defaultDate;

    //does not work :/
    foreach ($days as $keyD => $day) {
        foreach ($day->parts as $keyP => $part) {
            if ((clone $defaultDate)->add($part->duration) < (clone $defaultDate)->add($minDuration))
                $days[$keyD]->parts[$keyP]->availability = Availability::Full;
        }
    }

    // //todo merge stuff

    foreach ($days as $key => $day) {
        if ($day->parts) {
            $allFull = true;
            foreach ($day->parts as $part) {
                if ($part->availability == Availability::Full)
                    $days[$key]->availability = Availability::Partial;
                else {
                    $allFull = false;
                    if ($days[$key]->availability == Availability::Partial)
                        break;
                }
            }
            if ($allFull)
                $days[$key]->availability = Availability::Full;
        }
    }

    Tilex::Return($days);
});

function array_any(array $array, callable $fn)
{
    foreach ($array as $value) {
        if ($fn($value)) {
            return true;
        }
    }
    return false;
}

$app->Post('appointment', function (string $token, array $dates) {
    if ($token == null || strlen($token) < 1)
        Tilex::Error("Kein oder fehlerhafter Token übergeben");

    $appointment = Appointment::Get($token);

    if ($appointment == null)
        Tilex::Error("Ungültiger Token übergeben.");

    if ($appointment->isTaken)
        Tilex::Error("Termine wurden bereits festgelegt.");

    if (count($dates) != count($appointment->parts)) {
        Tilex::Error("Es wurden zu viel oder zu wenig Termine ausgewählt");
    }

    $service = new Google_Service_Calendar(GoogleCalendar::getClient($appointment->user_id));

    $approvedDates = array();

    foreach ($dates as &$date) {
        $date['from'] = new DateTime($date['from']);
        $date['to'] = new DateTime($date['to']);

        if (empty(getEvents($appointment->user_id, $date['from'], $date['to'], $service))) {
            $approvedDates[] = array('from' => $date['from']->format('c'), 'to' => $date['to']->format('c'));
        }
    }

    for ($i = 0; $i < count($dates); $i++) {
        $d = $dates[$i]['to']->getTimestamp() - $dates[$i]['from']->getTimestamp();

        if ($d > $appointment->parts[$i]->duration * 60 * 60)
            Tilex::Error('Der Termin Nr. ' . ($i + 1) . ' ist zu lang! (' . $d . '/' . ($appointment->parts[$i]->duration * 60 * 60) . ')');
    }

    if (count($approvedDates) != count($dates))
        Tilex::Return($approvedDates, 400);

    for ($i = 0; $i < count($dates); $i++) {
        $event = new Google_Service_Calendar_Event(array(
            'summary' => $appointment->subject,
            //'location' => '800 Howard St., San Francisco, CA 94103',
            //'description' => 'A chance to hear more about Google\'s developer products.',
            'start' => array(
                'dateTime' => $dates[$i]['from']->format('c'),
            ),
            'end' => array(
                'dateTime' => $dates[$i]['to']->format('c'),
            ),
            // 'recurrence' => array(
            //     'RRULE:FREQ=DAILY;COUNT=2'
            // ),
            // 'reminders' => array(
            //     'useDefault' => FALSE,
            //     'overrides' => array(
            //         array('method' => 'email', 'minutes' => 24 * 60),
            //         array('method' => 'popup', 'minutes' => 10),
            //     ),
        ),);

        $part = $appointment->parts[$i];
        $part->fromDate = $dates[$i]['from'];
        $part->toDate = $dates[$i]['to'];
        $part->Update();

        $service->events->insert(
            Calendar::GetPrimaryFromUser($appointment->user_id),
            $event,
            array(
                'sendUpdates' => 'all'
            )
        );
    }

    Tilex::Return($dates);
});

function getEvents(int $userId, DateTime $start, DateTime $end, Google_Service_Calendar $service = null)
{
    if ($service == null)
        $service = new Google_Service_Calendar(GoogleCalendar::getClient($userId));

    $allResults = array();
    foreach (Calendar::GetFromUser($userId) as $calendar) {
        //get all the events for the given Timeframe
        $calendarId = $calendar->calendar;
        $optParams = array(
            'maxResults' => 2500,
            'orderBy' => 'startTime',
            'timeMin' => $start->format('c'),
            'timeMax' => $end->format('c'),
            'singleEvents' => true,
        );
        $allResults = array_merge($allResults, $service->events->listEvents($calendarId, $optParams)->getItems());
    }

    if (!empty($allResults)) {
        foreach ($allResults as $event) {
            $event->isWholeDay = empty($event->start->dateTime);
            if ($event->isWholeDay) {
                $event->start = DateTime::createFromFormat('Y-m-d G:i:s', $event->start->date . ' 0:00:00');
                $event->end = DateTime::createFromFormat('Y-m-d G:i:s', $event->end->date . ' 0:00:00');
            } else {
                $event->start = DateTime::createFromFormat('Y-m-d\TH:i:s+', $event->start->dateTime);
                $event->end = DateTime::createFromFormat('Y-m-d\TH:i:s+', $event->end->dateTime);
            }
        }

        usort($allResults, function ($a, $b) {
            return $a->start > $b->start;
        });
        return $allResults;
    }
}
