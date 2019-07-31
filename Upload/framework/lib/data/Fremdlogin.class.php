<?php


class Fremdlogin {
    private $id = 0;
    
    /**
     * 
     * @var user
     */
    private $user = null;
    
    /**
     * 
     * @var user
     */
    private $adminUser = null;
    
    /**
     * 
     * @var string
     */
    private $message = "";
    
    /**
     * 
     * @var integer
     */
    private $time = 0;
    
    public function __construct($data) {
        $this->id = $data['fremdloginID'];
        $this->user = user::getUserByID($data['userID']);
        $this->adminUser = user::getUserByID($data['adminUserID']);
        $this->message = $data['loginMessage'];
        $this->time = $data['loginTime'];
    }
    
    public function getID() {
        return $this->id;
    }
    
    public function getUser() {
        return $this->user;
    }
    
    public function getAdminUser() {
        return $this->adminUser;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getTime() {
        return $this->time;
    }
    
    public function delete() {
        DB::getDB()->query("DELETE FROM fremdlogin WHERE fremdloginID='" . $this->getID() . "'");
    }
    
    public static function getMyFremdlogin() {
        $data = DB::getDB()->query_first("SELECT * FROM fremdlogin WHERE userID='" . DB::getSession()->getUser()->getUserID() . "'");
        
        if($data['fremdloginID'] > 0) {
            return new Fremdlogin($data);
        }
        
        return null;
    }
    
    /**
     * 
     * @param user $user
     * @param String $message
     */
    public static function createFremdlogin($user, $message) {
        DB::getDB()->query("INSERT INTO fremdlogin (userID, adminUserID, loginMessage, loginTime) values('" . $user->getUserID() . "','" . DB::getSession()->getUser()->getUserID() . "','" . DB::getDB()->escapeString($message) . "',UNIX_TIMESTAMP())");
    }
    
}
