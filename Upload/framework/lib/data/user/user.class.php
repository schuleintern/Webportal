<?php


use OTPHP\TOTP;

class user {

  private $isTeacher = false;
  private $isPupil = false;
  private $isEltern = false;
  private $isNone = false;

  private $teacherObject = null;
  private $pupilObject = null;
  private $elternObject = null;

  private $avatar = null;

  private $data;

  /**
   *
   * @var usergroup[]
   */
  private $groups = [];

  public function __construct($data) {
    $this->data = $data;

    $this->groups = PAGE::getFactory()->getGroupsByUserID( $this->data['userID'] );
    // $groups = DB::getDB()->query("SELECT * FROM users_groups WHERE userID='" . $this->data['userID'] . "'");
    // while($gr = DB::getDB()->fetch_array($groups)) {
    //   $this->groups[] = usergroup::getGroupByName($gr['groupName']);
    // }

    // Is Teacher
    $lehrer = PAGE::getFactory()->getLehrerByID( $this->data['userID'] );
    // $lehrer = DB::getDB()->query_first("SELECT * FROM lehrer WHERE lehrerUserID='" . $this->data['userID'] . "'");
    if($lehrer['lehrerID'] != "") {
      $this->isTeacher = true;
      $this->teacherObject = new lehrer($lehrer);
    }

    if(!$this->isTeacher) {
      // Is Schüler
      $schueler = PAGE::getFactory()->getSchuelerByID( $this->data['userID'] );
      //$schueler = DB::getDB()->query_first("SELECT * FROM schueler WHERE schuelerUserID='" . $this->data['userID'] . "'");
      if($schueler['schuelerUserID'] != "") {
        $this->isPupil = true;
        $this->pupilObject = new schueler($schueler);
      }
    }


    // Is Eltern
    $eltern = PAGE::getFactory()->getElternEmailByUserID( $this->data['userID'] );
    //$eltern = DB::getDB()->query_first("SELECT * FROM eltern_email WHERE elternUserID='" . $this->data['userID'] . "'");
    if($eltern['elternSchuelerAsvID'] != "") {
      $this->isEltern = true;
      $this->elternObject = new eltern($this->data['userID']);
    }

    if (!$this->isTeacher && !$this->isPupil && !$this->isEltern) {
      $this->isNone = true;
    }
  }

  public function getUserName() {
    return $this->data['userName'];
  }

  public function getFirstName() {
      return $this->data['userFirstName'];
  }

  public function userCanChangePassword() {
      return $this->data['userCanChangePassword'] > 0;
  }

  public function isSyncedUser() {
      return $this->data['userRemoteUserID'] != "";
  }

  public function getLastName() {
      return $this->data['userLastName'];
  }

  public function getUserID() {
    return $this->data['userID'];
  }

  public function getLastLoginTime() {
      return $this->data['userLastLoginTime'];
  }

  public function setLastLoginTimeNow() {
      DB::getDB()->query("UPDATE users SET userLastLoginTime=UNIX_TIMESTAMP() WHERE userID='" . $this->getUserID() . "'");
  }

  public function getFailedLoginCount(){
      return $this->data['userFailedLoginCount'];
  }

  public function getData($key) {
    return $this->data[$key];
  }

  public function getEMail() {
    return $this->data['userEMail'];
  }

  public function getGroupNames() {
    $ret = [];
    for($i = 0; $i < sizeof($this->groups); $i++) {
      $ret[] = $this->groups[$i]->getName();
    }

    return $ret;
  }

  public function isPupil() {
    return $this->isPupil;
  }

  public function isTeacher() {
    return $this->isTeacher;
  }

  public function isEltern() {
    return $this->isEltern;
  }

  public function isNone() {
    return $this->isNone;
  }

  public function getUserTyp($system = false) {

    if ( $this->isPupil ) {
        if (!$system) {
            return 'Schüler';
        } else {
            return 'isPupil';
        }
    }
    if ( $this->isTeacher ) {
        if (!$system) {
            return 'Lehrer';
        } else {
            return 'isTeacher';
        }
    }
    if ( $this->isEltern ) {
        if (!$system) {
            return 'Eltern';
        } else {
            return 'isEltern';
        }
    }
    if ( $this->isNone ) {
        if (!$system) {
            return 'Mitarbeiter';
        } else {
            return 'isNone';
        }
    }

    return '';
  }

  public function getCollection() {
      $collection = [
          "id" => $this->getUserID(),
          "vorname" => $this->getFirstName(),
          "nachname" => $this->getLastName(),
          "name" => $this->getDisplayName(),
          "type" => $this->getUserTyp(true),
          "avatar" => $this->getAvatar(true)
      ];
      if ($this->isPupil()) {
          $collection['klasse'] = $this->getPupilObject()->getKlasse();
      }
      return $collection;
  }
  
  public function getAllInklMail() {
      if($this->data['userMailCreated'] != "") {
          return $this->data['userMailCreated'];
      }
      else return null;
  }
  
  public function getAllInklMailPassword() {
      if($this->data['userMailInitialPassword'] != "") {
          return $this->data['userMailInitialPassword'];
      }
      else return null;
  }

  public function setPassword($password) {
      DB::getDB()->query("UPDATE users SET userCachedPasswordHash='" . DB::getDB()->escapeString(login::hash($password)) . "', userCachedPasswordHashTime=UNIX_TIMESTAMP() WHERE userID='" . $this->getUserID() . "'");
  }
  
  public function setAllInklMailCreated($mail, $password) {
      DB::getDB()->query("UPDATE users SET userMailCreated='" . DB::getDB()->escapeString($mail) . "', userMailInitialPassword='" . DB::getDB()->escapeString($password) . "' WHERE userID='" . $this->getUserID() . "'");
  }

  public function changeMail($newMail) {
      DB::getDB()->query("UPDATE users SET userEMail='" . DB::getDB()->escapeString($newMail) . "' WHERE userID='" . $this->getUserID() . "'");

      if($this->isEltern()) {
          DB::getDB()->query("UPDATE users SET userName='" . DB::getDB()->escapeString($newMail) . "' WHERE userID='" . $this->getUserID() . "'");
          DB::getDB()->query("UPDATE eltern_email SET elternEMail='" . DB::getDB()->escapeString($newMail) . "' WHERE elternUserID='" . $this->getUserID() . "'");
      }
  }

  public function canChangeMailAddress() {
      if($this->isSyncedUser()) {
          return false;
      }

      if($this->isEltern()) {
          return (DB::getGlobalSettings()->elternUserMode == 'ASV_CODE');
      }

      return true;
  }

  public function deleteUser() {
      DB::getDB()->query("DELETE FROM users WHERE userID='" . $this->getUserID() . "'");
      DB::getDB()->query("DELETE FROM users_groups WHERE userID='" . $this->getUserID() . "'");
      if($this->isEltern()) {
          DB::getDB()->query("DELETE FROM eltern_email WHERE elternUserID='" . $this->getUserID() . "'");
      }

      DB::getDB()->query("DELETE FROM sessions WHERE sessionUserID='" . $this->getUserID() . "'");       // Logout aus allen aktiven Sessions
  }

  /**
   * @return boolean
   */
  public function isAnyAdmin() {

    if($this->isAdmin()) return true;

    $allPages = requesthandler::getAllowedActions();

    $groups = [];
    for($i = 0; $i < sizeof($allPages); $i++) {
      if($allPages[$i]::getAdminGroup() != "") $groups[] = $allPages[$i]::getAdminGroup();
    }

    for($i = 0; $i < sizeof($groups); $i++) {
      if(in_array($groups[$i],$this->getGroupNames())) return true;
    }

    return false;
  }

  public function isAdmin() {
    return in_array("Webportal_Administrator", $this->getGroupNames());
  }

    /**
     *
     * @return string|unknown
     */
    public function getKlasse() {
        return false;
    }

  /**
   *
   * @return lehrer
   */
  public function getTeacherObject() {
    return $this->teacherObject;
  }

  public function getPupilObject() {
    return $this->pupilObject;
  }

  public function getElternObject() {
    return $this->elternObject;
  }

  public function getDisplayName() {
    return $this->data['userFirstName'] . " " . $this->data['userLastName'];
  }

  public function getDisplayNameWithFunction() {
    return $this->getDisplayName() . " (" . $this->getUserName() . ") (" . (($this->isTeacher) ? ("Lehrer") : (($this->isPupil) ? ("Schüler") : (($this->isEltern) ? ("Eltern") : ("Sonstige")))) . ")";
  }

  public function isMember($group) {
    return in_array($group, $this->getGroupNames());
  }


    public function getAvatar() {
      if (!$this->avatar) {
          $image = DB::getDB()->query_first("SELECT uploadID FROM image_uploads WHERE uploadUserName LIKE '" . $this->getUserName() . "'");
          if($image['uploadID'] > 0) {
              //$this->avatar = "index.php?page=userprofileuserimage&getImage=profile";
              $upload = new UploadImage($image['uploadID']);
              $this->avatar = 'data:image/jpeg;base64,'.$upload->getBase64();
          } else {
              $this->avatar = "cssjs/images/userimages/default.png";
          }

      }
      return $this->avatar;
    }


  /**
   * Format: +49XXXXXXXXXXX
   * @return unknown
   */
  public function getMobile() {
    return $this->data['userMobilePhoneNumber'];
  }

  public function getPasswordHash() {
      return $this->data['userCachedPasswordHash'];
  }

  public function receiveEMail() {
      return $this->data['userReceiveEMail'] > 0;
  }

  public function setReceiveEMail($receive) {
      DB::getDB()->query("UPDATE users SET userReceiveEMail='" . (($receive) ? 1 : 0) . "' WHERE userID='" . $this->getUserID() . "'");
  }

  public function isSekretariat() {
      return schulinfo::isVerwaltung($this);
  }


  public function has2FA() {
      
      // Falls global abgeschaltet, dann auch nicht für einen einzelnen Benutzer aktiv.
      if(!TwoFactor::is2FAActive()) return false;
      
      return $this->data['userTOTPSecret'] != "";
  }


  public function set2FA($secret) {
      
      if($secret == "" || $secret == null) {
          $this->removeAllTrustedDevices();
          // Alle Sessions mit aktiver 2FA beenden.
          
          DB::getDB()->query("DELETE FROM sessions WHERE sessionUserID ='" . $this->getUserID() . "' AND session2FactorActive > 0");
      }
      
      DB::getDB()->query("UPDATE users SET userTOTPSecret='" . DB::getDB()->escapeString($secret) . "' WHERE userID='" . $this->getUserID() . "'");
  }

  public function check2FACode($code) {
      if($this->has2FA()) {
          
          $code = str_replace("-", "", $code);
          
          if(strlen($code) < 6) return false;

          $totp = TOTP::create($this->data['userTOTPSecret']);

          // 30 Sekunden vorher

          $time = time();

          if($totp->verify($code, $time - 30)) {
              return true;
          }

          if($totp->verify($code, $time + 30)) {
              return true;
          }

          if($totp->verify($code, $time)) {
              return true;
          }

          return false;

      }

      return false;
  }

  /**
   * Ist 2FA generell aktiv?
   */
  public function is2FAActive() {
      return $this->data['user2FAactive'] > 0;
  }
  
  public function getSignature() {
      return nl2br($this->data['userSignature']);
  }
  
  public function getRawSignature() {
      return ($this->data['userSignature']);
  }
  
  public function setSignature($sig) {
      $sig = htmlspecialchars($sig);
      DB::getDB()->query("UPDATE users SET userSignature='" . DB::getDB()->escapeString($sig) . "' WHERE userID='" . $this->getUserID() . "'");
      
  }

  public function getAutoresponseText() {
    return nl2br($this->data['userAutoresponseText']);
  }
  
  public function getRawAutoresponseText() {
    return ($this->data['userAutoresponseText']);
  }

  public function setAutoresponseText($sig) {
    $sig = htmlspecialchars($sig);
    DB::getDB()->query("UPDATE users SET userAutoresponseText='" . DB::getDB()->escapeString($sig) . "' WHERE userID='" . $this->getUserID() . "'");
    
  }

  public function getAutoresponse() {
    return $this->data['userAutoresponse'];
  }

  public function getAutoresponseChecked() {
    if ( $this->data['userAutoresponse']) {
      return ' checked="checked"';
    } else {
      return '';
    }
  }
  

  public function setAutoresponse($value) {
    //echo $value; exit;
    $value = intval($value);
    DB::getDB()->query("UPDATE users SET userAutoresponse='" . DB::getDB()->escapeString($value) . "' WHERE userID='" . $this->getUserID() . "'");
    
  }



  public function addCurrentDeviceToTrustedDevices() {
      $cookieKey = base64_encode(random_bytes(500));
      $cookieKey = substr($cookieKey, 0,100);

      if(DB::isDebug()) {
          // Im Debug kein Secure Cookie
          setcookie("sitd" . $this->getUserID(), $cookieKey, time() + 365 * 24 * 60 * 60, "/", null, false, true);
      }
      else {
          setcookie("sitd" . $this->getUserID(), $cookieKey, time() + 365 * 24 * 60 * 60, "/", null, true, true);
      }

      DB::getDB()->query("INSERT INTO two_factor_trusted_devices (deviceCookieData, deviceUserID) values('" . $cookieKey ."','" . $this->getUserID() . "')");
  }
  
  public function removeCurrentDeviceFromTrustedDevices() {
      if($this->isCurrentDeviceTrusted()) {
          DB::getDB()->query("DELETE FROM two_factor_trusted_devices WHERE deviceCookieData='" . DB::getDB()->escapeString($_COOKIE['sitd' . $this->getUserID()]) . "'");
          setcookie("sitd" . $this->getUserID(), null, time()-3600);
      }
  }
  
  
  public function removeAllTrustedDevices() {
      DB::getDB()->query("DELETE FROM two_factor_trusted_devices WHERE deviceUserID='" . $this->getUserID() . "'");
  }

  public function isCurrentDeviceTrusted() {
      if(isset($_COOKIE['sitd' . $this->getUserID()])) {
          $device = DB::getDB()->query_first("SELECT * FROM two_factor_trusted_devices WHERE deviceCookieData='" . DB::getDB()->escapeString($_COOKIE['sitd' . $this->getUserID()]) . "'");
                    
          if($device['deviceID'] > 0 && $device['deviceUserID'] == $this->getUserID()) {
              return true;
          }
      }
      
      return false;
  }


  /**
   *
   * @param int $userID
   * @return user
   */
  public static function getUserByID($userID) {
    if($userID == 0) return new user(['userID' => 0, 'userName' => 'System', 'userFirstName' => 'System', 'userLastName' => 'Nachricht']);
    
    $data = PAGE::getFactory()->getUserByID( $userID );
    //$data = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . $userID . "'");
    if($data['userID'] > 0) {
      return new user($data);
    }

    return null;
  }

  /**
   *
   * @return user
   */
  public static function getSystemUser() {
      return self::getUserByID(0);
  }

  /**
   *
   * @param String $mail
   * @return user|NULL
   */
  public static function getUserByEMail($mail) {

      $data = DB::getDB()->query_first("SELECT * FROM users WHERE userEMail LIKE '" . $mail . "'");
      if($data['userID'] > 0) {
          return new user($data);
      }

      return null;
  }





  /**
   *
   * @param int $userID
   * @return user
   */
  public static function getByASVID($asvID) {

      $data = PAGE::getFactory()->getUserByASV( $asvID );
      //$data = DB::getDB()->query_first("SELECT * FROM users WHERE userAsvID='" . $asvID . "'");
      if($data['userID'] > 0) {
          return new user($data);
      }

      return null;
  }

  /**
   *
   * @param String $username
   * @return user|NULL
   */
  public static function getByUsername($username) {
      $data = DB::getDB()->query_first("SELECT * FROM users WHERE userName LIKE '" . DB::getDB()->escapeString($username) . "'");
      if($data['userID'] > 0) {
          return new user($data);
      }

      return null;
  }


  /**
   *
   * @return user[]
   */
  public static function getAll() {
    $data = DB::getDB()->query("SELECT * FROM users");

    $all = [];
    while($u = DB::getDB()->fetch_array($data)) {
      $all[] = new user($u);
    }

    return $all;
  }

  /**
   * @return user[]
   */
  public static function getAllEltern() {
      $data = DB::getDB()->query("SELECT * FROM users WHERE userNetwork='SCHULEINTERN_ELTERN'");

      $all = [];
      while($u = DB::getDB()->fetch_array($data)) {
          $all[] = new user($u);
      }

      return $all;
  }

  public static function getCountSchueler() {
      $count = DB::getDB()->query_first("SELECT COUNT(*) FROM users WHERE userID IN (SELECT schuelerUserID FROM schueler)");
      return $count[0];
  }

  public static function getCountLehrer() {
      $count = DB::getDB()->query_first("SELECT COUNT(*) FROM users WHERE userID IN (SELECT lehrerUserID FROM lehrer)");
      return $count[0];
  }

  public static function getCountEltern() {
      $count = DB::getDB()->query_first("SELECT COUNT(*) FROM users WHERE userNetwork='SCHULEINTERN_ELTERN'");
      return $count[0];
  }
}
