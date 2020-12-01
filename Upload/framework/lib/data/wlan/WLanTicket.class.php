<?php

class WLanTicket {
    private $data = [];
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function getID() {
        return $this->data['ticketID'];
    }
    
    public function getTicketText() {
        return $this->data['ticketText'];
    }
    
    public function isAssigned() {
        return $this->data['ticketAssignedTo'] > 0;
    }
    
    /**
     * 
     * @return user|NULL
     */
    public function getAssignedUser() {
        return user::getUserByID($this->data['ticketAssignedTo']);
    }
    
    /**
     * 
     * @return string
     */
    public function getAssignedDateAsNaturalDate() {
        return DateFunctions::getNaturalDateFromMySQLDate($this->data['ticketAssignedDate']);
    }

    /**
     * @return int
     */
    public function getDuration() {
        return $this->data['ticketValidMinutes'];
    }
    
    /**
     * 
     * @param user $user
     */
    public function assignToUser($user) {
        DB::getDB()->query("UPDATE wlan_ticket SET
            ticketAssignedTo='" . $user->getUserID() . "',
            ticketAssignedDate=CURDATE(),
            ticketAssignedBy='" . DB::getSession()->getUser()->getUserID() . "'
            WHERE ticketID='" . $this->getID() . "'
        ");
    }
    
    public function setName($name) {
        DB::getDB()->query("UPDATE wlan_ticket SET
            ticketName='" . DB::getDB()->escapeString($name) . "'
            WHERE ticketID='" . $this->getID() . "'
        ");
    }
    
    public function getName() {
        return $this->data['ticketName'];
    }
    
    public function getType() {
        return $this->data['ticketType'];
    }
    
    /**
     * 
     */
    public function delete() {
        DB::getDB()->query("DELETE FROM wlan_ticket WHERE ticketID='" . $this->getID() . "'");
    }
    
    /**
     * 
     * @param String $type SCHUELER, GAST
     * @param int $duration
     * @return WLanTicket|NULL
     */
    public static function getNextTicket($type, $duration) {
        $data = DB::getDB()->query_first("SELECT * FROM wlan_ticket WHERE ticketAssignedTo=0 AND ticketType='$type' AND ticketValidMinutes='" . intval($duration) . "'");
        
        if($data['ticketID'] > 0) {
            return new WLanTicket($data);
        }
        else return null;
    }
    
    /**
     * 
     * @param String $type SCHUELER, GAST
     * @return unknown[]
     */
    public static function getAvailibleDurations($type) {
        $times = [];
        
        $data = DB::getDB()->query("SELECT DISTINCT ticketValidMinutes FROM wlan_ticket WHERE ticketAssignedTo=0 AND ticketType='$type'");
        
        while($d = DB::getDB()->fetch_array($data)) $times[] = $d[0];
        
        return $times;
    }    
    
    /**
     * 
     * @return WLanTicket[]
     */
    public static function getMyTickets() {
        $userID = DB::getSession()->getUserID();
        
        $tickets = [];
        
        $datas = DB::getDB()->query("SELECT * FROM wlan_ticket WHERE ticketAssignedTo='" . $userID . "' ORDER BY ticketAssignedDate DESC");
    
        while($d = DB::getDB()->fetch_array($datas)) {
            $tickets[] = new WLanTicket($d);
        }
        
        return $tickets;    
    }
    
    public static function getAll() {
        
        $tickets = [];
        
        $datas = DB::getDB()->query("SELECT * FROM wlan_ticket ORDER BY ticketAssignedDate DESC");
        
        while($d = DB::getDB()->fetch_array($datas)) {
            $tickets[] = new WLanTicket($d);
        }
        
        return $tickets;    
    }
    
    /**
     * 
     * @param FileUpload $file
     */
    public static function uploadSophosFile($file, $type) {
        $data = file($file->getFilePath());

        for($i = 1; $i < sizeof($data); $i++) {
            $line = explode(";",str_replace("\"","",$data[$i]));
            
            $minutes = str_replace(" Minuten", "", $line[3]);
            
            DB::getDB()->query("INSERT INTO wlan_ticket (ticketText, ticketType, ticketValidMinutes) values
                (
                    '" . DB::getDB()->escapeString($line[0]) . "',
                    '" . DB::getDB()->escapeString($type) . "',
                    '" . DB::getDB()->escapeString($minutes) . "'
                )
            ");
        }
    }
}