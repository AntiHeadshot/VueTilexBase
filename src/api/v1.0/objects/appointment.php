<?php

class Appointment implements JsonSerializable
{
    /** id
     * @var int
     */
    public $id;

    /** the user the appoinzment is associated to
     * @var int
     */
    public $user_id;

    /** The token the appointment is connected to
     * @var string
     */
    public $token;

    /** The token the appointment is connected to
     * @var string
     */
    public $subject;

    /** The token the appointment is connected to
     * @var array
     */
    public $parts;

    /** The token the appointment is connected to
     * @var boolean
     */
    public $isTaken;

    public static function New(int $user_id, string $subject)
    {
        $appointment = new self();
        $appointment->user_id = $user_id;
        $appointment->token = base64_encode(random_bytes(32));
        $appointment->subject = $subject;
        $appointment->parts = array();
        $appointment->isTaken = null;
        return $appointment;
    }


    // From JsonSerializable
    public function jsonSerialize()
    {
        $data = [
            'id' => (int)$this->id,
            'user_id' => (int)$this->user_id,
            'token' => $this->token,
            'subject' => $this->subject,
            'parts' => $this->parts,
        ];

        if (isset($this->url))
            $data['url'] = $this->url;

        return $data;
    }

    public function AddPart(int $duration)
    {
        $this->parts[] = AppointmentPart::New($this, $duration);
    }

    function Insert()
    {
        global $pdo;

        if ($_SESSION['userid'] != $this->user_id)
            Tilex::Error("You can not provide appointments issued for an other user!");

        $statement = $pdo->prepare("SELECT * FROM appointments WHERE token = :token");
        $statement->execute(array('token' => $this->token));
        $appointment = $statement->fetch();

        if ($appointment !== false) {
            Tilex::Error('Bitte erneut erzeugen');
        }

        $statement = $pdo->prepare("INSERT INTO appointments (user_id, token, subject) VALUES(:userId, :token, :subject)");

        $statement->bindValue(":userId", $this->user_id, PDO::PARAM_INT);
        $statement->bindValue(":token", $this->token, PDO::PARAM_STR);
        $statement->bindValue(":subject", $this->subject, PDO::PARAM_STR);

        $result = $statement->execute();

        if (!$result)
            Tilex::Error("Could not create Appointment!");

        $this->id =  $pdo->lastInsertId();

        foreach ($this->parts as $part) {
            $part->Insert();
        }

        return $this;
    }

    function Delete()
    {
        Tilex::Error("Appointments can not be deleted.");
    }

    static function Get($token)
    {
        global $pdo;

        $statement = $pdo->prepare("SELECT * FROM appointments WHERE token = :token");
        $result = $statement->execute(array('token' => $token));

        if (!$result)
            Tilex::Error("Could not get appointment!");

        $appointment = $statement->fetchObject(__CLASS__);
        if (!$appointment)
            return null;
        $appointment->parts = AppointmentPart::Get($appointment->id);

        $appointment = (object) array_merge(
            (array)$appointment,
            array('isTaken' => false)
        );

        foreach ($appointment->parts as $part)
            if ($part->fromDate != null) {
                $appointment->isTaken = true;
                break;
            }

        return $appointment;
    }
}

class AppointmentPart implements JsonSerializable
{
    /** id
     * @var int
     */
    public $id;

    /** the user the appoinzment is associated to
     * @var int
     */
    public $appointment_id;

    /** the user the appoinzment is associated to
     * @var Appointment
     */
    public $appointment;

    /** The token the appointment is connected to
     * @var int
     */
    public $duration;

    /** The token the appointment is connected to
     * @var DateTime
     */
    public $fromDate;

    /** The token the appointment is connected to
     * @var DateTime
     */
    public $toDate;

    public static function New(Appointment $appointment, string $duration)
    {
        $appointmentPart = new self();
        $appointmentPart->duration = $duration;
        $appointmentPart->appointment = $appointment;

        return $appointmentPart;
    }

    // From JsonSerializable
    public function jsonSerialize()
    {
        $data = [
            'id' => (int)$this->id,
            'appointment_id' => (int)$this->appointment_id,
            'duration' => (int)$this->duration,
            'fromDate' => $this->fromDate == null ? null : $this->fromDate->format(DATE_ISO8601),
            'toDate' => $this->toDate == null ? null : $this->toDate->format(DATE_ISO8601),
        ];

        if (isset($this->urlGoogle))
            $data['urlGoogle'] = $this->urlGoogle;
        if (isset($this->urlOutlook))
            $data['urlOutlook'] = $this->urlOutlook;
        if (isset($this->urlYahoo))
            $data['urlYahoo'] = $this->urlYahoo;

        return $data;
    }

    function Insert()
    {
        global $pdo;

        $statement = $pdo->prepare("INSERT INTO appointmentParts (appointment_id, duration) VALUES(:appointment_id, :duration)");

        $statement->bindValue(":appointment_id", $this->appointment_id ? $this->appointment_id : $this->appointment->id, PDO::PARAM_INT);
        $statement->bindValue(":duration", $this->duration, PDO::PARAM_INT);

        $result = $statement->execute();

        if (!$result)
            Tilex::Error("Could not save part of Appointment!");

        $this->id = $pdo->lastInsertId();

        return $this;
    }

    function Update()
    {
        global $pdo;

        $statement = $pdo->prepare("UPDATE appointmentParts SET fromDate = :fromDate, toDate = :toDate WHERE id = :id");
        $statement->bindValue(":fromDate", gmdate("Y-m-d H:i:s", $this->fromDate->getTimestamp()), PDO::PARAM_STR);
        $statement->bindValue(":toDate", gmdate("Y-m-d H:i:s", $this->toDate->getTimestamp()), PDO::PARAM_STR);
        $statement->bindValue(":id", $this->id, PDO::PARAM_INT);
        $result = $statement->execute();

        if (!$result)
            Tilex::Error("Could not update Appointment, update failed!");

        return $this;
    }

    function Delete()
    {
        Tilex::Error("Appoiintments can not be deleted.");
    }

    static function Get($appointment_id)
    {
        global $pdo;

        $statement = $pdo->prepare("SELECT * FROM appointmentParts WHERE appointment_id = :appointment_id");
        $result = $statement->execute(array('appointment_id' => $appointment_id));

        if (!$result)
            Tilex::Error("Could not get appointment!");

        $rows = $statement->fetchAll(PDO::FETCH_CLASS, __CLASS__);

        foreach ($rows as &$row) {
            if ($row->fromDate != null)
                $row->fromDate = DateTime::createFromFormat('Y-m-d G:i:s', $row->fromDate,  new DateTimeZone('UTC'));
            if ($row->toDate != null)
                $row->toDate = DateTime::createFromFormat('Y-m-d G:i:s', $row->toDate, new DateTimeZone('UTC'));
        }

        return $rows;
    }
}
