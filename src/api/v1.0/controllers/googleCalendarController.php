<?php

$app->Get('calendar/auth', function (string $error = null, string $code = null) {
  AssurePrivilege(Privilege::Moderator);

  GoogleCalendar::authClient($_SESSION['userid']);
});

$app->Get('calendar/register', function (string $error = null, string $code = null, int $state = null) {
  AssurePrivilege(Privilege::Moderator);

  if ($state != $_SESSION['userid'])
    Tilex::Error("Du bist nicht wer du vorgibst zu sein!");
  if ($error != null) {
    header('Location: ' . $_SERVER['SERVER_NAME'] . '?error=' . $error);
    die();
  }

  GoogleCalendar::signAuthClient($_SESSION['userid'], $code);

  header('Location: http://' . $_SERVER['SERVER_NAME'] . '?success=calendar_connect');
  Tilex::Return(true);
});

$app->Post('calendar/update', function () {
  AssurePrivilege(Privilege::Moderator);

  $client = GoogleCalendar::getClient($_SESSION['userid']);

  $service = new Google_Service_Calendar($client);

  $calendars = $service->calendarList->listCalendarList()->getItems();

  foreach ($calendars as $calendar) {
    Calendar::New($_SESSION['userid'], $calendar->summaryOverride ? $calendar->summaryOverride : $calendar->summary, $calendar->id, $calendar->primary == true, $calendar->selected == true)
      ->Insert();
  }

  Tilex::Return(true);
});

$app->Post('calendar/kill', function () {
  AssurePrivilege(Privilege::Moderator);
  GoogleCalendar::getClient($_SESSION['userid'])->revokeToken();
  Calendar::DeleteFromUser($_SESSION['userid']);
  GoogleCalendar::killCalendar($_SESSION['userid']);
});

$app->Get('calendar', function () {
  AssurePrivilege(Privilege::Moderator);

  Tilex::Return(Calendar::GetAll($_SESSION['userid']));
});

$app->Post('calendar/{id}', function (int $id, bool $isActive = null, bool $isPrimary = null) {
  AssurePrivilege(Privilege::Moderator);

  $calendar = Calendar::Get($id, $_SESSION['userid']);

  if ($isActive !== null)
    $calendar->isActive = $isActive;
  if ($isPrimary !== null)
    $calendar->isPrimary = $isPrimary;

  $calendar->Update();
  Tilex::Return($calendar);
});

class GoogleCalendar
{

  static function isConnected(int $userId)
  {
    $isConnected = true;

    $client = GoogleCalendar::_getClient($userId, function ($client) use (&$isConnected) {
      $isConnected = false;
    });
    return $isConnected;
  }

  /**
   * Returns an authorized API client.
   * @return Google_Client the authorized client object
   */
  static function getClient(int $userId)
  {
    $client = GoogleCalendar::_getClient($userId, function ($client) {
      Tilex::Error("Ohjeh, die verbindung zum Kalender konnte nicht hergestellt werden. Bitte versuche es später erneut.\nSollte das Problem weiter bestehen, kontaktiert mich.");
    });
    return $client;
  }

  /**
   * Returns an authorized API client.
   * @return Google_Client the authorized client object
   */
  static function authClient(int $userId)
  {
    GoogleCalendar::_getClient($userId, function ($client) {
      // Request authorization from the user.
      Tilex::Return($client->createAuthUrl());
    });
  }

  /**
   * Returns an authorized API client.
   * @return Google_Client the authorized client object
   */
  static function signAuthClient(int $userId, string $authCode)
  {
    GoogleCalendar::_getClient($userId, function ($client) use (&$authCode) {
      // Exchange authorization code for an access token.
      $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
      // Check to see if there was an error.
      if (array_key_exists('error', $accessToken)) {
        Tilex::Return("Fehler beim Verbinden mit Google Calender: " . join(', ', $accessToken));
      }

      $client->setAccessToken($accessToken);

      $service = new Google_Service_Calendar($client);

      $calendars = $service->calendarList->listCalendarList()->getItems();

      foreach ($calendars as $calendar) {
        Calendar::New($_SESSION['userid'], $calendar->summary, $calendar->id, $calendar->primary == true, $calendar->selected == true)
          ->Insert();
      }
    });
  }

  private static function _getClient(int $userId, $ifRefreshFails)
  {
    $client = new Google_Client();
    $client->setApplicationName('Inkment');
    $client->setScopes(array(Google_Service_Calendar::CALENDAR_EVENTS, Google_Service_Calendar::CALENDAR_READONLY));
    $client->setAuthConfig('../../../config/credentials.json');
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    $client->setPrompt('select_account consent');
    $client->setState($userId);

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.

    $accessTokenRaw = GoogleCalendar::getAccessTokenForUser($userId);
    if ($accessTokenRaw != null) {
      $accessToken = json_decode($accessTokenRaw, true);
      if ($accessToken != null)
        $client->setAccessToken($accessToken);
    }

    if ($client->isAccessTokenExpired()) {

      // Refresh the token if possible, else fetch a new one.
      if ($client->getRefreshToken()) {
        $refreshToken = $client->getRefreshToken();
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

        $accessTokenUpdated = $client->getAccessToken();
        // append refresh token
        $accessTokenUpdated['refresh_token'] = $refreshToken;
        //Set the new acces token
        $client->setAccessToken($accessTokenUpdated);
      } else
        $ifRefreshFails($client);

      // Save the token
      GoogleCalendar::setAccessTokenForUser($userId, json_encode($client->getAccessToken()));
    }

    return $client;
  }

  private static function getAccessTokenForUser(int $userId)
  {
    global $pdo;

    $statement = $pdo->prepare("SELECT token FROM googleapitokens WHERE user_id = :id");
    if ($statement->execute(array('id' => $userId))) {
      $token = $statement->fetch(PDO::FETCH_ASSOC);

      return $token["token"];
    } else {
      return null;
    }
  }

  private static function setAccessTokenForUser(int $userId, string $token)
  {
    global $pdo;

    $statement = $pdo->prepare("INSERT INTO googleapitokens (user_id, token) VALUES(:id, :token) ON DUPLICATE KEY UPDATE token = :token");
    return $statement->execute(array('id' => $userId, 'token' => $token));
  }

  public static function killCalendar(int $userId)
  {
    global $pdo;

    $statement = $pdo->prepare("DELETE FROM googleapitokens WHERE user_id = :id");
    return $statement->execute(array('id' => $userId));
  }
}
