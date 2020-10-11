<?php

class usergroup {

  private static $allGroups = [];

  private $name;

  private $members = [];
  
  private $isOwnGroup = false;
  
  private $ownGroupData = [];

  private function __construct($name) {
    $this->name = $name;
    self::$allGroups[] = $this;
    // $ownGroupData = PAGE::getFactory()->getUserGroupsOwnByName( DB::getDB()->escapeString($name) );
    $ownGroupData = DB::getDB()->query_first("SELECT * FROM users_groups_own WHERE groupName='" . DB::getDB()->escapeString($name) . "'");
    
    if($ownGroupData['groupName'] != "") {
        $this->isOwnGroup = true;
        $this->ownGroupData = $ownGroupData;
    }
  }
  
  public function getName() {
  	return $this->name;
  }
  
  public function isOwnGroup() {
      return $this->isOwnGroup;
  }
  
  public function canContactByTeacher() {
      return $this->ownGroupData['groupContactTeacher'] > 0;
  }
  
  public function isMessageRecipient() {
      return $this->ownGroupData['groupIsMessageRecipient'] > 0;
      
      
  }
  
  public function canContactByPupil() {
      return $this->ownGroupData['groupContactPupil'] > 0;
  }
  
  public function canContactByParents() {
      return $this->ownGroupData['groupContactParents'] > 0;
  }
  
  public function setCanContactByTeacher($status) {
      DB::getDB()->query("UPDATE users_groups_own SET groupContactTeacher='" . $status . "' WHERE groupName='" . $this->getName() . "'");
  }
  
  public function setCanContactByParents($status) {
      DB::getDB()->query("UPDATE users_groups_own SET groupContactParents='" . $status . "' WHERE groupName='" . $this->getName() . "'");
  }
  
  public function setCanContactByPupil($status) {
      DB::getDB()->query("UPDATE users_groups_own SET groupContactPupil='" . $status . "' WHERE groupName='" . $this->getName() . "'");
  }
  
  public function setIsMessageRecipient($status) {
      DB::getDB()->query("UPDATE users_groups_own SET groupIsMessageRecipient='" . $status . "' WHERE groupName='" . $this->getName() . "'");
  }
  
  public function hasNextCloudShare() {
      return $this->ownGroupData['groupHasNextcloudShare'] > 0;
  }
  
  public function setHasNextCloudShare($status) {
      DB::getDB()->query("UPDATE users_groups_own SET groupHasNextcloudShare='" . $status . "' WHERE groupName='" . $this->getName() . "'");
  }
  
  public function getNextCloudUser() {
      return null;
  } 
  
  public function addUser($userID) {
  	DB::getDB()->query("INSERT INTO users_groups (userID, groupName) values('" . DB::getDB()->escapeString($userID) . "','" . $this->name . "') ON DUPLICATE KEY UPDATE userID=userID");
  }
  
  public function removeUser($userID) {
  	DB::getDB()->query("DELETE FROM users_groups WHERE userID='" . DB::getDB()->escapeString($userID) . "' AND groupName='" . $this->name . "'");
  }
  
  /**
   * 
   * @param unknown $user
   * @return boolean
   */
  public function isMember($user) {
      $member = $this->getMembers();
      
      for($i = 0; $i < sizeof($member); $i++) {
          if($member[$i]->getUserID() == $user->getID()) return true;
      }
      
      return false;
  }

  /**
   * @return user[]
   */
  public function getMembers() {
    if(sizeof($this->members) == 0) {
        $users = DB::getDB()->query("SELECT * FROM users NATURAL JOIN users_groups WHERE groupName='" . DB::getDB()->escapeString($this->name) . "' ORDER BY userName ASC, userLastName ASC, userFirstName ASC");
    	while($u = DB::getDB()->fetch_array($users)) {
    		$this->members[] = new user($u);
    	}
    }
    
    return $this->members;
  }

  public static function getAllByUserID($userID) {

    $groupAnswer = [];

    $groups = DB::getDB()->query("SELECT * FROM users_groups WHERE userID='" . $userID . "'");

    while($g = DB::getDB()->fetch_array($groups)) {
      $found = false;
      for($i = 0; $i < sizeof(self::$allGroups); $i++) {
        if(self::$allGroups[$i]->name == $g['groupName']) {
          $groupAnswer[] = self::$allGroups[$i];
          $found = true;
        }
      }

      if(!$found) {
        $groupAnswer[] = new usergroup($g['groupName']);
      }
    }

  }

  public static function getGroupByName($group) {
    for($i = 0; $i < sizeof(self::$allGroups); $i++) {
      if(self::$allGroups[$i]->name == $group) {
        return self::$allGroups[$i];
      }
    }

    return new usergroup($group);
  }
  
  public static function getGroupByChecksum($checksum) {
      
      self::getAllOwnGroups(); // Lokalen Vektor initialisieren.
      
      for($i = 0; $i < sizeof(self::$allGroups); $i++) {
          $cs = md5(self::$allGroups[$i]->name);
          if($cs == $checksum) {
              return self::$allGroups[$i];
          }
      }
      
      return null;
  }

  
  
  /**
   * 
   * @return usergroup[]
   */
  public static function getAllOwnGroups() {
      $data = DB::getDB()->query("SELECT * FROM users_groups_own");
      
      $all = [];
      
      while($d = DB::getDB()->fetch_array($data)) {
          $all[] = new usergroup($d['groupName']);
      }
      
      
      return $all;
  }
  
  public static function addOwnGroup($name) {
      DB::getDB()->query("INSERT INTO users_groups_own (groupName) values('" . DB::getDB()->escapeString($name) . "') ON DUPLICATE KEY UPDATE groupName=groupName");
  }
  
  public function deleteOwnGroup() {
      DB::getDB()->query("DELETE FROM users_groups_own WHERE groupName='" . DB::getDB()->escapeString($this->getName()) . "'");
      DB::getDB()->query("DELETE FROM users_groups WHERE groupName='" . DB::getDB()->escapeString($this->getName()) . "'");
      
  }
  
}

