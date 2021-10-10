<?php

abstract class Availability
{
  const Available = 1;
  const Partial = 2;
  const Full = 3;
  const Blocked = 4;

  public static function ToString($val)
  {
    switch ($val) {
      case 0:
        return "Unset";
      case Availability::Available:
        return "Available";
      case Availability::Partial:
        return "Partial";
      case Availability::Full:
        return "Full";
      case Availability::Blocked:
        return "Blocked";
    }
  }
}

class Date implements JsonSerializable
{
  /** Username of the current User
   * @var DateTime
   */
  public $day;

  /** The token itself
   * @var Availability
   */
  public $availability;

  /** The token itself
   * @var array
   */
  public $parts;

  public static function New(DateTime $day, int $availability = null)
  {
    $date = new self();
    $date->day = $day;
    $date->availability = $availability ? $availability : Availability::Available;
    $date->parts = array();
    return $date;
  }

  public function AddPart(DateInterval $from, DateInterval $duration, int $availability)
  {
    $this->parts[] = DatePart::New($from, $duration, $availability);
  }

  // From JsonSerializable
  public function jsonSerialize()
  {
    return [
      'day' => $this->day->format(DATE_ISO8601),
      'availability' => $this->availability,
      'parts' => $this->parts
    ];
  }
}

$defaultDate = new DateTime('2000-01-01');

class DatePart implements JsonSerializable
{
  /** Username of the current User
   * @var DateInterval
   */
  public $from;

  /** Username of the current User
   * @var DateInterval
   */
  public $duration;

  /** The token itself
   * @var Availability
   */
  public $availability;

  public static function New(DateInterval $from, DateInterval $duration, int $availability)
  {
    $datePart = new self();
    $datePart->from = $from;
    $datePart->duration = $duration;
    $datePart->availability = $availability;
    return $datePart;
  }


  // From JsonSerializable
  public function jsonSerialize()
  {
    global $defaultDate;

    $ret = [
      'from' => (clone $defaultDate)->add($this->from)->getTimestamp() - (clone $defaultDate)->getTimestamp(),
      'duration' => (clone $defaultDate)->add($this->duration)->getTimestamp() - (clone $defaultDate)->getTimestamp(),
      'availability' => $this->availability
    ];

    return $ret;
  }
}
