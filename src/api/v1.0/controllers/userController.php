<?php

//====================================================User Post====================================================
//-------------------------------------------------Authorisation---------------------------------------------------

$app->Post('login', function (string $email, string $password, int $duration = 60 * 60) {
  global $pdo;

  $statement = $pdo->prepare("SELECT * FROM users WHERE email = :email");
  $statement->execute(array('email' => $email));
  $user = $statement->fetch();

  //Überprüfung des Passworts
  if ($user !== false && password_verify($password, $user['password'])) {
    if (!$user['is_active']) {
      Tilex::Error("Der Benutzer wurde noch nicht freigeschalten.", 503);
    } else {
      $_SESSION['userid'] = $user['id'];
      $_SESSION['privilege'] = $user['privilege'];
    }
  } else {
    Tilex::Error("E-Mail oder Passwort war ungültig", 503);
  }

  $token = (Token::New($user['name'], $duration))->Insert($pdo);
  $_SESSION['token'] = $token->token;
  Tilex::Return($token);
});

$app->Post('logout', function () {
  if(isset($_SESSION['token']))
  Token::Delete($_SESSION['token']);
  session_unset();
  Tilex::Return("Auf wieder sehen!");
});

//-------------------------------------------------Create---------------------------------------------------
$app->Post('register', function (string $username, string $password, string $email) {
  global $pdo;

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Tilex::Error('Bitte eine gültige E-Mail-Adresse eingeben');
  }
  if (strlen($password) == 0) {
    Tilex::Error('Bitte ein Passwort angeben');
  }

  //Überprüfe, dass die E-Mail-Adresse noch nicht registriert wurde
  $statement = $pdo->prepare("SELECT * FROM users WHERE email = :email");
  $statement->execute(array('email' => $email));
  $user = $statement->fetch();

  if ($user !== false) {
    Tilex::Error('Diese E-Mail-Adresse ist bereits vergeben<br>');
  }

  $statement = $pdo->prepare("SELECT * FROM users WHERE name = :name");
  $statement->execute(array('name' => $username));
  $user = $statement->fetch();

  if ($user !== false) {
    Tilex::Error('Dieser Benutzername ist bereits vergeben<br>');
  }

  //Keine Fehler, wir können den Nutzer registrieren
  $passwort_hash = password_hash($password, PASSWORD_DEFAULT);

  $statement = $pdo->prepare("INSERT INTO users (email, name, password, privilege) VALUES (:email, :name, :password, 1)");
  $result = $statement->execute(array('email' => $email, 'name' => $username, 'password' => $passwort_hash));

  if (!$result) {
    Tilex::Error("Beim Abspeichern ist leider ein Fehler aufgetreten");
  }

  Tilex::Return("registered user {$username}({$email})");
});

//-------------------------------------------------Edit---------------------------------------------------
$app->Post('user', function (string $oldPassword, string $newPassword = null, string $email = null, string $username = null) {
  global $pdo;

  AssurePrivilege(Privilege::User);

  verifyPassword($oldPassword);

  if (isset($email)) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      Tilex::Error('Bitte eine gültige E-Mail-Adresse eingeben');
    }

    $statement = $pdo->prepare("SELECT * FROM users WHERE email = :email AND id <> :id");
    $statement->execute(array('email' => $email, 'id' => $_SESSION['userid']));
    $user = $statement->fetch();

    if ($user !== false) {
      Tilex::Error('Diese E-Mail-Adresse ist bereits vergeben<br>');
    }

    $statement = $pdo->prepare("UPDATE users SET email = :email WHERE id = :id");
    $result = $statement->execute(array('email' => $email, 'id' => $_SESSION['userid']));

    if (!$result) {
      Tilex::Error("Fehler beim Speichern der Email");
    }
  }

  if (isset($username)) {
    $statement = $pdo->prepare("SELECT * FROM users WHERE name = :name AND id <> :id");
    $statement->execute(array('name' => $username, 'id' => $_SESSION['userid']));
    $user = $statement->fetch();

    if ($user !== false) {
      Tilex::Error('Dieser Benutzername ist bereits vergeben<br>');
    }

    $statement = $pdo->prepare("UPDATE users SET name = :name WHERE id = :id");
    $result = $statement->execute(array('name' => $username, 'id' => $_SESSION['userid']));

    if (!$result) {
      Tilex::Error("Fehler beim Speichern des Namens");
    }
  }

  if (isset($newPassword)) {
    $passwort_hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $statement = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $result = $statement->execute(array('password' => $passwort_hash, 'id' => $_SESSION['userid']));

    if (!$result) {
      Tilex::Error("Fehler beim Speichern des Passwortes");
    }
  }

  Tilex::Return(true);
});

//-------------------------------------------------Edit---------------------------------------------------
$app->Post('user', function (string $password, string $username, string $key) {
  global $pdo;

  verifyTempkey($username, $key);

  $passwort_hash = password_hash($password, PASSWORD_DEFAULT);
  $statement = $pdo->prepare("UPDATE users SET password = :password WHERE name = :name");
  $result = $statement->execute(array('password' => $passwort_hash, 'name' => $username));

  if (!$result) {
    Tilex::Error("Fehler beim Speichern des Passwortes");
  }
});

/**
 * Updates user status and/or privileges.
 */
$app->Post('user/{userid}', function (int $userid, int $privilege = null, bool $isActive = null) {
  global $pdo;

  if ($privilege < Privilege::Moderator)
    AssurePrivilege(Privilege::Moderator);
  else
    AssurePrivilege(Privilege::Admin);

  if (isset($isActive)) {
    $statement = $pdo->prepare("UPDATE users SET is_active = :active WHERE id = :id");
    $statement->bindValue(":active", $isActive, PDO::PARAM_INT);
    $statement->bindValue(":id", $userid, PDO::PARAM_INT);
    if (!$statement->execute())
      Tilex::Error("Benutzer konnte nicht (in)aktiv geschalten werden.");
  }

  if (isset($privilege)) {
    $statement = $pdo->prepare("UPDATE users SET privilege = :privilege WHERE id = :id");
    if (!$statement->execute(array('privilege' => $privilege, 'id' => $userid)))
      Tilex::Error("Die Rechte des Benutzers konnte nicht gesetzt werden.");
  }
});


//====================================================User Get====================================================
$app->Get('user', function () {
  if (!isset($_SESSION['userid']))
    Tilex::Return(false);

  AssurePrivilege(Privilege::User);
  Tilex::Return(getUserExt($_SESSION['userid']));
});

$app->Get('user/{userId}', function (int $userId) {
  AssurePrivilege(Privilege::Moderator);

  Tilex::Return(getUser($userId));
});

/**
 * Returns users ordered by creation date.
 * Can only be used paged, if where are more than 100 Users.
 * @start The rownumber of the first user, not the Id.
 * @limit Used for paging (max = 100)
 */
$app->Get('users', function (int $start = 0, int $limit = 10) {
  if ($limit > 100)
    $limit = 100;
  Tilex::Return(getUsers($limit, $start));
});

//===================================================User Other===================================================
$app->Delete('user', function (string $password) {

  global $pdo;

  verifyPassword($password);

  deleteUser($_SESSION['userid']);
});

$app->Delete('user/{userId}', function (int $userId) {
  AssurePrivilege(Privilege::Admin);

  deleteUser($userId);

  Tilex::Return("delete user {$userId}");
});

function deleteUser($userId)
{
  global $pdo;

  $statement = $pdo->prepare("SELECT * FROM users WHERE id = :id");
  if (!$statement->execute(array('id' => $userId))) {
    Tilex::Error("User {$userId} could not be deleted");
  }
}

function getUser($id)
{
  global $pdo;

  $statement = $pdo->prepare("SELECT id,name,is_active,privilege FROM users WHERE id = :id");
  if ($statement->execute(array('id' => $id))) {
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    $user['isActive'] = (bool)$user['is_active'];
    return $user;
  } else {
    Tilex::Error("Benutzer {$id} konnte nicht geladen werden.");
  }
}

function getUserExt($id)
{
  global $pdo;

  $statement = $pdo->prepare("SELECT id,email,name,is_active,privilege FROM users WHERE id = :id");
  if ($statement->execute(array('id' => $id))) {
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    $user['isActive'] = (bool)$user['is_active'];
    $user['isConnected'] = GoogleCalendar::isConnected($id);

    return $user;
  } else {
    Tilex::Error("Benutzer {$id} konnte nicht geladen werden.");
  }
}

function getUsers($count, $start)
{
  global $pdo;

  $statement = $pdo->prepare("SELECT id,name,is_active,privilege FROM users ORDER BY created_at LIMIT :skip,:count");
  $statement->bindValue(":count", $count, PDO::PARAM_INT);
  $statement->bindValue(":skip", $start, PDO::PARAM_INT);
  if ($statement->execute()) {
    $users = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as &$user)
      $user['isActive'] = (bool)$user['is_active'];
    return $users;
  } else {
    Tilex::Error("{$count} Benutzer ab {$start} konnten nicht geladen werden.");
  }
}

function verifyPassword($password)
{
  global $pdo;

  $statement = $pdo->prepare("SELECT * FROM users WHERE id = :id");
  $statement->execute(array('id' => $_SESSION['userid']));
  $user = $statement->fetch();

  //Überprüfung des Passworts
  if ($user == false) {
    Tilex::Error("Benutzer wurde nicht gefunden.");
  }

  if (!password_verify($password, $user['password'])) {
    Tilex::Error("Passwort ist nicht korrekt.");
  }

  return $user;
}


function verifyTempkey($username, $key)
{
  global $pdo;

  $statement = $pdo->prepare("UPDATE users WHERE name = :name AND resetKey = :key");
  $statement->execute(array('name' => $username, 'key' => $key));
  if ($statement->rowCount() < 1)
    Tilex::Error("Token abgelaufen.");
}
