<?php

class NextCloudApi {
  private function __construct() {}


  private static function isEnabled() {
    return DB::getGlobalSettings()->enableNextCloud;
  }

  public static function createUser($username, $displayName, $email, $password) {
      
  	$username = Encoding::getUTF8($username);
  	$displayName = Encoding::getUTF8($displayName);
  	$email = Encoding::getUTF8($email);
  	$password = Encoding::getUTF8($password);
  	
    $data = array('userid' => $username, 'password' => $password);

    $result = self::getCurlContext(DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/users', "POST", $data);

    $success = false;

    if(strval($result->meta->status) != 'failure') {
      $success = true;
      if(!self::updateAttribute($username, "quota", DB::getGlobalSettings()->nextCloudQuota)) {
        $success = false;
      }
    }



    if($success) {
      if(self::updateAttribute($username, "display", $displayName) && $email != "") {
        if(!self::updateAttribute($username, "email", $email)) {
          $success = false;
        }
      }
    }


    return $success;
  }
  
  public static function updatePassword($username, $password) {
      $username = Encoding::getUTF8($username);
      $password = Encoding::getUTF8($password);
      
      return self::updateAttribute($username, "password", $password);
  }

  public static function deleteUser($username) {
  	
  	$username = Encoding::getUTF8($username);

    $url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/users/' . $username;
    $result = self::getCurlContext($url, "DELETE", []);
    
    return strval($result->meta->status) != 'failure';
  }

  private static function updateAttribute($username, $key, $value) {
    $data = array('key' => $key, 'value' => $value);

    $url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/users/' . $username;
    $result = self::getCurlContext($url, "PUT", $data);

    return strval($result->meta->status) != 'failure';
  }

  public static function getCurrentGroups() {
    $url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/groups?search=';

    $result = self::getCurlContext($url, "GET", []);
    
    $all = $result->data->groups->element;

    $groups = [];
    for($i = 0; $i < sizeof($all); $i++) {
      $groups[] = strval($all[$i]);
    }

    return $groups;
  }
  
  public static function deleteGroup($group) {
  	
  	$group = Encoding::getUTF8($group);
  	
  	$url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/groups/' . $group;
  	$result = self::getCurlContext($url, "DELETE", []);
  	
  	return strval($result->meta->status) != 'failure';
  }
  
  public static function addGroup($group) {
  	
  	$group = Encoding::getUTF8($group);
  	 
  	$url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/groups';
  	$result = self::getCurlContext($url, "POST", ['groupid' => $group]);
  	 
  	return strval($result->meta->status) != 'failure';
  }
  
  public static function getAllUsers() {
  	$url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/users';
  	$result = self::getCurlContext($url, "GET", []);
  	  	
  	$all = $result->data->users->element;

    $users = [];
    for($i = 0; $i < sizeof($all); $i++) {
    	if(strval($all[$i]) != "owncloudadmin" && strval($all[$i]) != "nextcloudadmin" && strval($all[$i]) != "admin") {
      		$users[] = strval($all[$i]);
    	}
    }

    return $users;
  }
  
  public static function getAllGroupsForUser($user) {
  	
  	$user = Encoding::getUTF8($user);
  	 
	$url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/users/' . $user . '/groups';
	
  	$result = self::getCurlContext($url, "GET", []);
  		 
  	$all = $result->data->groups->element;
  	
  	$users = [];
  	for($i = 0; $i < sizeof($all); $i++) {
  		$users[] = strval($all[$i]);
  	}
  	
  	return $users;
  }
  
  public static function addUserToGroup($user, $group) {
  	$user = Encoding::getUTF8($user);
  	$group = Encoding::getUTF8($group);
  	 
  	$url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/users/' . $user . '/groups';
  	$result = self::getCurlContext($url, "POST", ['groupid' => $group]);
  	  	
  	return strval($result->meta->status) != 'failure';
  }
  
  public static function removeUserFromGroup($user, $group) {
  	$user = Encoding::getUTF8($user);
  	$group = Encoding::getUTF8($group);
  	
  	$url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/users/' . $user . '/groups';
  	$result = self::getCurlContext($url, "DELETE", ['groupid' => $group]);
  	 
  	return strval($result->meta->status) != 'failure';
  }
  
  public static function getMembersOfGroup($group) {
      $group = Encoding::getUTF8($group);
      
      
      $url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/cloud/groups/' . ($group) . '';

      
      $result = self::getCurlContext($url, "GET", []);
      
      
      $all = $result->data->users->element;
      
      $users = [];
      for($i = 0; $i < sizeof($all); $i++) {
          $users[] = strval($all[$i]);
      }
      
      return $users;
      
  }
  
  public static function shareFolderWithGroup($user, $password, $folder, $group) {
  	$user = Encoding::getUTF8($user);
  	$group = Encoding::getUTF8($group);
  	$folder = Encoding::getUTF8($folder);
  	
  	$url = DB::getGlobalSettings()->nextCloudHost . '/ocs/v1.php/apps/files_sharing/api/v1/shares';
  	$data = [
  		'path' => $folder,
  		'shareType' => 1,
  		'shareWith' => $group,
  		'permissions' => 31
  	];
  	
  	$result = self::getCurlContext($url, "POST", $data, $user, $password);
  	
  	return strval($result->meta->status) != 'failure';
  }
  
  public static function createFolderForUser($user, $password, $folder) {
  	return WebDav::createFolder(DB::getGlobalSettings()->nextCloudHost, $user, $password, $folder);
  }

  /**
   *
   * @param String $url komplette URL
   * @param String $method POST oder PUT
   * @param String[][] $data Daten
   * @return SimpleXMLElement XML Antwort
   */
  private static function getCurlContext($url, $method, $data, $user = NULL, $password = NULL) {

    $query = http_build_query($data, '', '&');
    $process = curl_init($url);
    curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'OCS-APIREQUEST: true'));
    curl_setopt($process, CURLOPT_HEADER, 0);
    if($user == NULL) curl_setopt($process, CURLOPT_USERPWD, DB::getGlobalSettings()->nextCloudAuth['user'] . ":" . DB::getGlobalSettings()->nextCloudAuth['password']);
    else curl_setopt($process, CURLOPT_USERPWD, $user . ":" . $password);
    curl_setopt($process, CURLOPT_TIMEOUT, 30);
    if($method == "POST") curl_setopt($process, CURLOPT_POST, true);
    else if($method == "GET") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "GET");
    else if($method == "PUT") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PUT");
    else if($method == "DELETE") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
    else if($method == "PATCH") curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PATCH");
    
    
    curl_setopt($process, CURLOPT_POSTFIELDS, $query);
    curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

    $return = curl_exec($process);

    curl_close($process);

    return simplexml_load_string($return);
  }


}
