<?php

class Calendar implements JsonSerializable
{
    /** ID
     * @var int
     */
    public $id;

    /** Id of owner
     * @var int
     */
    public $user_id;

    /** the id of this calendar
     * @var string
     */
    public $calendar;

    /** the name of this calendar
     * @var string
     */
    public $name;

    /** Wheather this calendar is used to save dates
     * @var bool
     */
    public $isPrimary;

    /** Wheather this calendar is used for finding free time
     * @var bool
     */
    public $isActive;

    public static function New($userId, $name, $calendarId, $isPrimary, $isActive)
    {
        $token = new self();
        $token->user_id = $userId;
        $token->calendar = $calendarId;
        $token->name = $name;
        $token->isPrimary = $isPrimary;
        $token->isActive = $isActive;
        return $token;
    }

    // From JsonSerializable
    public function jsonSerialize()
    {
        return [
            'id' => (int)$this->id,
            'user_Id' => $this->user_id,
            'calendar' => $this->calendar,
            'name' => $this->name,
            'isPrimary' => boolval($this->isPrimary),
            'isActive' => boolval($this->isActive),
        ];
    }

    function Insert()
    {
        global $pdo;

        $statement = $pdo->prepare("SELECT * FROM googleapicalendar WHERE user_id = :userId AND calendar = :calendar");
        $statement->bindValue(":userId", $this->user_id, PDO::PARAM_INT);
        $statement->bindValue(":calendar", $this->calendar, PDO::PARAM_STR);
        $statement->execute();
        $existing = $statement->fetchObject(__CLASS__);

        if ($existing) {
            $existing->name = $this->name;
            $existing->Update();
            return $existing;
        } else {
            $statement = $pdo->prepare("INSERT INTO googleapicalendar (user_id, calendar, name, isPrimary, isActive) VALUES(:userId, :calendar, :name, :isPrimary, :isActive)");

            $statement->bindValue(":userId", $this->user_id, PDO::PARAM_INT);
            $statement->bindValue(":calendar", $this->calendar, PDO::PARAM_STR);
            $statement->bindValue(":name", $this->name, PDO::PARAM_STR);
            $statement->bindValue(":isPrimary", $this->isPrimary, PDO::PARAM_BOOL);
            $statement->bindValue(":isActive", $this->isActive, PDO::PARAM_BOOL);

            $result = $statement->execute();

            if (!$result)
                Tilex::Error("Could not save token, login failed!");
        }
        return $this;
    }

    function Update()
    {
        global $pdo;

        if ($_SESSION['userid'] != $this->user_id)
            Tilex::Error("This does not belong to you!");

        if ($this->isPrimary) {
            $statement = $pdo->prepare("UPDATE googleapicalendar SET isPrimary = 0 WHERE user_id = :user_id");
            $result = $statement->execute(array('user_id' => $this->user_id));

            if (!$result)
                Tilex::Error("Could not save token, login failed!");
        }

        $statement = $pdo->prepare("UPDATE googleapicalendar SET calendar = :calendar, name = :name, isPrimary = :isPrimary , isActive = :isActive WHERE id = :id");
        $statement->bindValue(":id", $this->id, PDO::PARAM_INT);
        $statement->bindValue(":calendar", $this->calendar, PDO::PARAM_STR);
        $statement->bindValue(":name", $this->name, PDO::PARAM_STR);
        $statement->bindValue(":isPrimary", $this->isPrimary, PDO::PARAM_BOOL);
        $statement->bindValue(":isActive", $this->isActive, PDO::PARAM_BOOL);
        $result = $statement->execute();

        if (!$result)
            Tilex::Error("Could not save token, login failed!");

        return $this;
    }

    function Delete()
    {
        global $pdo;

        if ($_SESSION['userid'] != $this->user_id)
            Tilex::Error("This does not belong to you!");

        $statement = $pdo->prepare("DELETE FROM googleapicalendar WHERE id = :id");
        $result = $statement->execute(array('id' => $this->Id));

        if (!$result)
            Tilex::Error("Could not delete calendar.");
    }

    static function Get($id, $userId): Calendar
    {
        global $pdo;

        $statement = $pdo->prepare("SELECT * FROM googleapicalendar WHERE user_id = :userId AND id = :id");
        $result = $statement->execute(array('id' => $id, 'userId' => $userId));

        if (!$result)
            Tilex::Error("Could not save token, login failed!");

        return $statement->fetchObject(__CLASS__);
    }

    static function GetFromUser($userId): array
    {
        global $pdo;

        $statement = $pdo->prepare("SELECT * FROM googleapicalendar WHERE user_id = :userId AND isActive");
        $result = $statement->execute(array('userId' => $userId));

        if (!$result)
            Tilex::Error("Could not save token, login failed!");

        $rows = $statement->fetchAll(PDO::FETCH_CLASS, __CLASS__);

        return $rows;
    }

    static function GetPrimaryFromUser($userId): string
    {
        global $pdo;

        $statement = $pdo->prepare("SELECT calendar FROM googleapicalendar WHERE user_id = :userId AND isPrimary");
        $result = $statement->execute(array('userId' => $userId));

        if (!$result)
            Tilex::Error("Could not save token, login failed!");

        return $statement->fetch()['calendar'];
    }

    static function GetAll($userId): array
    {
        global $pdo;

        $statement = $pdo->prepare("SELECT * FROM googleapicalendar WHERE user_id = :userId");
        $result = $statement->execute(array('userId' => $userId));

        if (!$result)
            Tilex::Error("Could not save token, login failed!");

        $rows = $statement->fetchAll(PDO::FETCH_CLASS, __CLASS__);

        return $rows;
    }

    static function DeleteFromuser($userId)
    {
        global $pdo;

        if ($_SESSION['userid'] != $userId)
            Tilex::Error("This does not belong to you!");

        $statement = $pdo->prepare("DELETE FROM googleapicalendar WHERE user_id = :userId");
        $result = $statement->execute(array('userId' => $userId));

        if (!$result)
            Tilex::Error("Could not delete calendar.");
    }
}
