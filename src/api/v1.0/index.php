<?php

date_default_timezone_set('Europe/Berlin');

require __DIR__ . '/composer/vendor/autoload.php';

include_once 'internal/connect.php';
include_once 'internal/tilex.php';

include_once 'objects/token.php';
include_once 'objects/calendar.php';
include_once 'objects/date.php';
include_once 'objects/appointment.php';
include_once 'objects/user.php';

include_once 'internal/jsonBody.php';
include_once 'internal/secret.php';

$app = new Tilex();

$app->Default(
  function ($path, $method) {
    Tilex::Error("Invalid Call", 404);
  }
);

/**
 * Returns this documentation.
 */
$app->Get('', function () {
  global $app;
  Tilex::Return($app->GetDocumentation());
});

include 'controllers/userController.php';

include 'controllers/googleCalendarController.php';// before data, contains static Class

//include 'controllers/dateController.php';

include 'controllers/appointmentController.php';


$app->Call(); 
