<?php

/**
 * User einer Nextcloud Installation
 * 
 * @author Christian Spistchka
 *
 */
class NextCloudUser {
    private $nextcloudUsername = "";
    private $nextcloudQuota = 0;
    
    private $data = [];
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function getUserID() {
        return $this->data['userID'];
    }
    
    
    /**
     * 
     * @param user $user
     */
    public static function getNextCloudUser($user) {
        $data = DB::getDB()->query_first("SELECT * FROM nextcloud_users WHERE userID='" . $user->getUserID() . "'");
        
        if($data['userID'] > 0) {
            return new NextCloudUser($data);
        }
        
        return null;
    }
}

