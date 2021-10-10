<?php

class User
{

  /** Username of the current User
   * @var string
   */
  public $username;

  /** The token itself
   * @var string
   */
  public $token;

  /** The date of expiration as Unix timestamp
   * @var int
   */
  public $vlaidUntil;

  public $id;
  public $name;
  public $email;
  public $is_active;
  public $privilege;

  static function Get($id)
  {
    global $pdo;

    $statement = $pdo->prepare("SELECT id,name,email,is_active,privilege FROM users WHERE id = :id");
    $result = $statement->execute(array('id' => $id));

    if (!$result)
      Tilex::Error("Could not get user!");

    return $statement->fetchAll(PDO::FETCH_CLASS, __CLASS__);
  }
}