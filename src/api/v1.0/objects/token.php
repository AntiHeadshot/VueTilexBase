<?php

class Token
{
  private $access_token;
  private $identifier;

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

  public static function New($username, $duration)
  {
    $token = new self();
    $token->username = $username;
    $token->access_token =  base64_encode(random_bytes(128));
    $token->identifier = base64_encode($username);
    $token->token = "{$token->access_token}.{$token->identifier}";
    $token->vlaidUntil = time() + $duration;
    return $token;
  }

  public static function FromToken($tokenString)
  {
    $token = new self();

    $parts = explode('.', $tokenString);
    if (count($parts) < 2)
      return null;
    $token->access_token =  $parts[0];
    $token->identifier = $parts[1];
    $token->username = base64_decode($token->identifier);
    $token->token = $tokenString;
    $token->vlaidUntil = null;
    return $token;
  }

  function Insert()
  {
    global $pdo;
    
    $statement = $pdo->prepare("INSERT INTO tokens (value, user_id) VALUES(:value, (SELECT id FROM users where name = :username))");
    $result = $statement->execute(array('value' => $this->token, 'username' => $this->username));

    if(!$result)
      Tilex::Error("Could not save token, login failed!");
      
    return $this;
  }
  
  static function Delete(string $token)
  {
    global $pdo;

    $statement = $pdo->prepare("DELETE FROM tokens WHERE value = :token");
    $result = $statement->execute(array('token' => $token));

    if (!$result)
      Tilex::Error("Could not delete calendar.");
  }
}
