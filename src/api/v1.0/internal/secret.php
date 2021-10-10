<?php
include_once 'session_start.php';
include_once "enums.php";

if (!isset($_SESSION['userid'])) {

    $token = null;
    $headers = apache_request_headers();
    if (isset($_COOKIE['Authorization'])) {
        $token = $_COOKIE['Authorization'];

        $statement = $pdo->prepare("SELECT NAME, u.id, privilege, is_active  FROM users u JOIN tokens ON u.id = user_id WHERE VALUE = :value");
        $statement->execute(array('value' => $token));
        $user = $statement->fetch();

        if ($user !== false) {
            if ($user['is_active']) {
                $_SESSION['userid'] = $user['id'];
                $_SESSION['privilege'] = $user['privilege'];
                $_SESSION['token'] = $token;
            }
        }
    }
}

function AssurePrivilege(int $privilege)
{
    if (!isset($_SESSION['userid'])) {
        Tilex::Error('Bitte zuerst <a href="/login">einloggen</a>', 401);
    }

    $currentPrivilege = $_SESSION['privilege'];
    if ($currentPrivilege < $privilege) {
        Tilex::Error('Unzureichende Berechtigung! Eingeloggt als ' . Privilege::ToString($currentPrivilege) . ', ' . Privilege::ToString($privilege) . ' benötigt.', 403);
    }
}
