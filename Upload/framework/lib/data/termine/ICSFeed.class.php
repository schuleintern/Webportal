<?php

use \Eluceo\iCal\Domain\Entity\Event;
use \Eluceo\iCal\Domain\ValueObject\Location;
use \Eluceo\iCal\Domain\ValueObject\Date;
use \Eluceo\iCal\Domain\ValueObject\DateTime;
use \Eluceo\iCal\Domain\ValueObject\TimeSpan;
use \Eluceo\iCal\Domain\ValueObject\SingleDay;
use \Eluceo\iCal\Domain\ValueObject\MultiDay;

class ICSFeed {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function getID() {
        return $this->data['feedID'];
    }
    
    public function isKlassenkalender() {
        return $this->data['feedType'] == 'KL';
    }
    
    public function isAndererKalender() {
        return $this->data['feedType'] == 'AK';
    }
    
    public function isExternerKalender() {
        return $this->data['feedType'] == 'EK';
    }
    
    public function getFeedData() {
        return $this->data['feedData'];
    }
    
    public function getUserID() {
        return $this->data['feedUserID'];
    }
    
    public function getKey() {
        return $this->data['feedKey'];
    }
    
    public function getKey2() {
        return $this->data['feedKey2'];
    }
    
    public function getURL() {
        return DB::getGlobalSettings()->urlToIndexPHP . "?page=geticsfeed&a=" . $this->getID() . "&b=" . $this->getKey() . "&c=" . $this->getKey2() . "&d=q1w2/feed.ics";
    }
    
    public static function getKlassenkalenderFeed($klasse, $userID, $withEx) {
        $feedData = json_encode([
            'klasse' => $klasse,
            'withEx' => $withEx
        ]);
        
        return self::getFeedByUserIDFeedTypeAndFeedData($userID, "KL", $feedData);
    }
    
    public static function getAndererKalenderFeed($kalenderID, $userID) {
        $feedData = $kalenderID;
        
        return self::getFeedByUserIDFeedTypeAndFeedData($userID, "AK", $feedData);
    }
    
    public static function getExternerKalenderFeed($kalenderID, $userID) {
        $feedData = $kalenderID;
        
        return self::getFeedByUserIDFeedTypeAndFeedData($userID, "EK", $feedData);
    }
    
    private static function getFeedByUserIDFeedTypeAndFeedData($userID, $feedType, $feedData) {
        $feed = DB::getDB()->query_first("SELECT * FROM icsfeeds WHERE feedUserID='" . $userID . "' AND feedType='$feedType' AND feedData='" . DB::getDB()->escapeString($feedData) . "'");
        
        if($feed['feedID'] > 0) return new ICSFeed($feed);
        else {
            return self::createFeedAndReturnObject($feedType, $feedData, $userID);
        }
    }
    
    private static function createFeedAndReturnObject($feedType, $feedData, $userID) {
        $key1 = self::random_str(40);
        $key2 = self::random_str(40);
        
        DB::getDB()->query("INSERT INTO icsfeeds (feedType, feedData, feedKey, feedKey2, feedUserID) values('$feedType','" . DB::getDB()->escapeString($feedData) . "','" . $key1 . "','" . $key2 . "','" . DB::getDB()->escapeString($userID) . "')");
        
        $newID = DB::getDB()->insert_id();
        
        return self::getByID($newID);
        
        
    }
    
    public static function getByID($id) {
        $feed = DB::getDB()->query_first("SELECT * FROM icsfeeds WHERE feedID='" . intval($id). "'");
        
 
        if($feed['feedID'] > 0) return new ICSFeed($feed);
        else return null;
        
    }
    
    public static function getByIDAndKeys($id, $key1, $key2) {
        $feed = DB::getDB()->query_first("SELECT * FROM icsfeeds WHERE feedID='" . intval($id). "' AND feedKey='" . DB::getDB()->escapeString($key1) . "' AND feedKey2='" . DB::getDB()->escapeString($key2) . "'");
             
        
        if($feed['feedID'] > 0) return new ICSFeed($feed);
        else return null;
    }

    private static function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
    
    
    /**
     * @param DateTimeInterface $dateStart
     * @param DateTimeInterface $dateEnd
     * @param boolean $isAllDay
     * @return \Eluceo\iCal\Domain\ValueObject\Occurrence
     */
    private static function makeOccurrence($dateStart, $dateEnd, $isAllDay) {
        if ($isAllDay) {
            // clone the start and end dates and set their times to 00:00:00, just to be on the safe side.
            $start = (clone $dateStart)->setTime(0, 0);
            $end = (clone $dateEnd)->setTime(0, 0);
            if (date_diff($start, $end)->days === 0) {
                return new SingleDay(new Date($start));
            }

            return new MultiDay(new Date($start), new Date($end));
        }

        // we don't need to make any further adjustments, just take the start and end dates as-is
        return new TimeSpan(new DateTime($dateStart, true), new DateTime($dateEnd, true));
    }


    /**
     *
     * @param string $id unsued
     * @param string $title
     * @param DateTimeInterface $dateStart
     * @param DateTimeInterface $dateEnd
     * @param string $ort
     * @param string $beschreibung
     * @param boolean $isAllDay
     * @return \Eluceo\iCal\Domain\Entity\Event
     */
    public static function getICSFeedObject($id, $title, $dateStart, $dateEnd, $ort, $beschreibung, $isAllDay = true) {
        $vEvent = new Event();
        $vEvent
        ->setOccurrence(static::makeOccurrence($dateStart, $dateEnd, $isAllDay))
        ->setSummary($title)
        ->setLocation(new Location($ort))
        ->setDescription($beschreibung)
        ;
        
        return $vEvent;
        
        /*
        
        $text = "";
        $text .= "BEGIN:VEVENT\r\n";
        $text .= "UID:$id\r\n";
        $text .= "LOCATION:$ort\r\n";
        $text .= "SUMMARY:$title\r\n";
        $text .= "DESCRIPTION:$beschreibung\r\n";
        $text .= "CLASS:PUBLIC\r\n";
        $text .= "DTSTART" . (($isAllDay) ? (";VALUE=DATE:$dateStart") : (":$dateStart")) . "\r\n";
        if($dateEnd != null) {
            $text .= "DTEND" . (($isAllDay) ? (";VALUE=DATE:$dateEnd") : (":$dateEnd")) . "\r\n";
        }
        // $text .= "DTSTAMP:" . date("YmdZHi00Z") . "\r\n";
        $text .= "END:VEVENT\r\n";
        
        return $text;*/
        
        /**
            BEGIN:VEVENT
            UID:461092315540@example.com
            ORGANIZER;CN="Alice Balder, Example Inc.":MAILTO:alice@example.com
            LOCATION:Somewhere
            SUMMARY:Eine Kurzinfo
            DESCRIPTION:Beschreibung des Termines
            CLASS:PUBLIC
            DTSTART:20060910T220000Z
            DTEND:20060919T215900Z
            DTSTAMP:20060812T125900Z
            END:VEVENT
         */
    }

    /**
     * @param \Eluceo\iCal\Domain\Entity\Calendar $vCalendarObject
     */
    public static function sendICSFeed($vCalendarObject) {
        $factory = new NamedCalendarFactory();
        $rendered = $factory->createCalendar($vCalendarObject);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="cal.ics"');

        echo $rendered;
        exit(0);
    }
    
}

