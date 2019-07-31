<?php

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
    
    public function getByIDAndKeys($id, $key1, $key2) {
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
     * 
     * @param String $id unsued
     * @param String $title
     * @param DateTime $dateStart
     * @param DateTime $dateEnd
     * @param String $ort
     * @param String $beschreibung
     * @param boolean $isAllDay
     * @return \Eluceo\iCal\Component\Event
     */
    public static function getICSFeedObject($id, $title, $dateStart, $dateEnd, $ort, $beschreibung, $isAllDay=true) {
        
        
        
        $vEvent = new \Eluceo\iCal\Component\Event();
        $vEvent
        ->setDtStart($dateStart)
        ->setDtEnd($dateEnd)
        ->setNoTime($isAllDay)
        ->setSummary($title)
        ->setLocation($ort)
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
    
    public static function sendICSFeed($vCalendarObject) {
        
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="cal.ics"');
        
        echo $vCalendarObject->render();
        
        exit(0);
    }
    
}

