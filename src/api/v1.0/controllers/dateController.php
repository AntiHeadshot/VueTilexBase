<?php

use Spatie\CalendarLinks\Link;
use Spatie\CalendarLinks\Generators\Ics;

$app->Get('dates', function () {

  $from = DateTime::createFromFormat('Y-m-d H:i', '2018-02-01 09:00');
  $to = DateTime::createFromFormat('Y-m-d H:i', '2018-02-01 18:00');

  $link = Link::create('Sebastian\'s birthday', $from, $to)
    ->description('Vorpflege nicht vergessen ;)')
    ->address('Schönbuchstraße 19, 70565 Stuttgart');

  // Generate a link to create an event on Google calendar
  echo 'Google: ';
  echo $link->google();
  echo '     yahoo: ';
  // Generate a link to create an event on Yahoo calendar
  echo $link->yahoo();
  echo '     weboutlook: ';

  // Generate a link to create an event on outlook.com calendar
  echo $link->webOutlook();
  echo '     iCal & Outlook: ';

  $from2 = $from->add(new DateInterval('P2D'));
  $to2 = $to->add(new DateInterval('P2D'));

  $link2 = Link::create('Sebastian\'s birthday', $from2, $to2)
    ->description('Vorpflege nicht vergessen ;)')
    ->address('Schönbuchstraße 19, 70565 Stuttgart');

  // Generate a data uri for an ics file (for iCal & Outlook)

  $ics = (new Ics())->generateMultiple($link, $link2);

  echo $ics;
});

/**
 * return a skill by Id
 */
$app->Get('termine/{userId}/{jahr}/{monat}', function (int $userId, int $jahr, int $monat, int $tag = null) {
  $isFullMonth = $tag == null;

  if ($isFullMonth) {
    $start = DateTime::createFromFormat('Y.n.j G-i-s', $jahr . '.' . $monat . '.1 0-00-00');
    $end = (clone $start)->add(new DateInterval('P1M'))->sub(new DateInterval('PT1S'));
  } else {
    $start = DateTime::createFromFormat('Y.n.j G-i-s', '' . $jahr . '.' . $monat . '.' . $tag . ' 0-00-00');
    $end = (clone $start)->add(new DateInterval('PT24H'))->sub(new DateInterval('PT1S'));
  }

  $events = getEvents($userId, $start, $end);

  if (empty($events)) {
    //print "No upcoming events found.\n";
    Tilex::Return(array());
  } else {
    //print "Upcoming events:\n";
    Tilex::Return($events);
  }
});


