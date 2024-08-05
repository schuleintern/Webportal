<?php



class login extends AbstractPage {
    
    private $blockAfter = 5;
    private $blockFor = 5;
    
	public function __construct() {
		parent::__construct("Startseite");
		
    	if(DB::isloggedin() && $_REQUEST['authorize'] != 1) {
    		header("Location: index.php");
    		exit();
    	}
    	
    	if(DB::isloggedin()) {
    	    $answer = [
    	        'success' => true,
    	    ];
    	    
    	    echo json_encode($answer);
    	    exit(0);
    	}
    	
    	
	}

	public function execute() {
		
		$success = false;
		$isSingleSignOn = false;
		
		$errorMessage = "";
		
		$isAjaxRequest = true;

		$isDebug = DB::getGlobalSettings()->debugMode === true;


		if($_REQUEST['authorize'] > 0) {
			
			if($_POST['username'] == "" || $_POST['password'] == "") {
			    $errorMessage = 'Sie müssen einen Benutzernamen und ein Passwort eingeben!';
			}
			else {
    			$users = DB::getDB()->query("SELECT * FROM users WHERE userName LIKE '" . DB::getDB()->escapeString(trim(($_POST['username']))) . "'");
    			
    			$user = null;
    			
    			while($userLine = DB::getDB()->fetch_array($users)) {
    				if(strtolower($userLine['userName']) == strtolower(trim($_POST['username']))) {
    					$user = $userLine;
    				}
    			}
    			
    			// Genaue Übereinstimmung überwiegt.
    			$users = DB::getDB()->query("SELECT * FROM users WHERE userName LIKE '" . DB::getDB()->escapeString(trim(($_POST['username']))) . "'");
    						
    			while($userLine = DB::getDB()->fetch_array($users)) {
    				if($userLine['userName'] == trim($_POST['username'])) {
    					$user = $userLine;
    				}
    			}
    			
    			if($user == null) {
    			    $success = false;
    			    $errorMessage  = "Unbekannter Benutzer oder falsches Passwort!  ";
    			}
    			else if($user['userID'] == 0 || $user['userID'] == "") {
    			    $success = false;		// Benutzername unbekannt
    			    $errorMessage = "Unbekannter Benutzer oder falsches Passwort!";
    			}
    			elseif($isDebug && $_REQUEST['debugLogin'] > 0) {
    			    // Debug login immer erfolgreich
    			    $success = true;
                }
    			elseif($user['userCachedPasswordHash']  != "" && $user['userCachedPasswordHashTime'] >= $user['userLastPasswordChangeRemote']) {
    				
    				if($user['userFailedLoginCount'] > 2) {
    					if(!MathCaptcha::checkCaptcha($_POST['captchaID'], $_POST['a'], $_POST['captcha'])) {
    					    $errorMessage = "Das Captcha wurde leider falsch gelöst.";
    					}
    				}
    				
    				if(self::check_password($user['userCachedPasswordHash'], $_POST['password'])) {
    					$success = true;
    				}
    				else {
    				    $success = false;
    				    $errorMessage = "Unbekannter Benutzer oder falsches Passwort!";
    				}
    			}
    			else {
    				
    				if($user['userFailedLoginCount'] > 2) {
    					if(!MathCaptcha::checkCaptcha($_POST['captchaID'], $_POST['a'], $_POST['captcha'])) {
    					    $errorMessage  = "Das Captcha wurde leider falsch gelöst.";
    					}
    				}
    				
    				// Passworthash nicht mehr gültig oder nicht vorhanden
    				include_once("../framework/lib/remote/RemoteAccess.class.php");
    				
    				if(!in_array($user['userNetwork'], DB::getInternalNetworks())) {
    					$remote = new RemoteAccess($user['userNetwork']);
    								
    					$success = $remote->checkPassword($_POST['username'],$_POST['password']);
    						
    					if($success && $remote->getDirectoryType() == 'ACTIVEDIRECTORY') {
    						DB::getDB()->query("UPDATE users SET userCachedPasswordHash='" . self::hash($_POST['password']) . "', userCachedPasswordHashTime=UNIX_TIMESTAMP(), userFailedLoginCount=0 WHERE userID='" . $user['userID'] . "'");
    					}
    				}
    			}
			}
			
		}
		else {
		    eval("echo(\"".DB::getTPL()->get("login/index")."\");");
		    
		    PAGE::kill(false);
		}
		
		if($success != false) {
			// Create Session
			
            $session = session::loginAndCreateSession($user['userID'], $_POST['keepLogin']);
			
			
			if($isAjaxRequest) {
			    $answer = [
			        'success' => true
			    ];
			    
			    echo(json_encode($answer));
			    exit(0);
			}
			else {
			    header("Location: index.php");
			    exit(0);
			}
		}
		else {
			$message = "<div class=\"callout callout-danger\">
		          <p><strong>Die Anmeldung war leider nicht erfolgreich!</strong></p>
		        </div>
				";
			
			if($user['userID'] > 0) {
				DB::getDB()->query("UPDATE users SET userFailedLoginCount=userFailedLoginCount+1 WHERE userID='" . $user['userID'] . "'");
				$user['userFailedLoginCount']++;
			}
			
			$captchaHTML = "";
			if($user['userFailedLoginCount'] > 2) {
				// Captcha anzeigen
				$captchaHTML = MathCaptcha::getCaptureHTMLCode();
			}
			
			if($isAjaxRequest) {
			    $answer = [
			        'success' => false,
			        'errorMessage' => $errorMessage,
			        'captchaHTML' => $captchaHTML
			    ];
			    
			    echo(json_encode($answer));
			    exit(0);
			}
			else {
			    
			    
			    $valueusername = $_POST['username'];
			    
			    eval("echo(\"".DB::getTPL()->get("login/index")."\");");
			    
			    PAGE::kill(true);
			}

		}
		
	}
	
	// HASHING
    private static $algo = '$2a';
    private static $cost = '$10';
 
    public static function unique_salt() {
        return substr(sha1(mt_rand()),0,22);
    }
 
    public static function hash($password) {
 
        return crypt($password,
                    self::$algo .
                    self::$cost .
                    '$' . self::unique_salt());
 
    }
 
    public static function check_password($hash, $password) {
        $full_salt = substr($hash, 0, 29);
        $new_hash = crypt($password, $full_salt);
        return ($hash == $new_hash);
    }
   
    // /HASHING
	
	private static function getGradeFromGroups($groups) {
		$gString = array();
		for($i = 0; $i < sizeof($groups); $i++) {
			if(substr($groups[$i], 0, 3) == "G_5" && strlen($groups[$i]) == 4) {
				$gString[] = str_replace("G_", "", $groups[$i]);
			}
			if(substr($groups[$i], 0, 3) == "G_6" && strlen($groups[$i]) == 4) {
				$gString[] = str_replace("G_", "", $groups[$i]);
			}
			if(substr($groups[$i], 0, 3) == "G_7" && strlen($groups[$i]) == 4) {
				$gString[] = str_replace("G_", "", $groups[$i]);
							}
			if(substr($groups[$i], 0, 3) == "G_8" && strlen($groups[$i]) == 4) {
				$gString[] = str_replace("G_", "", $groups[$i]);
							}
			if(substr($groups[$i], 0, 3) == "G_9" && strlen($groups[$i]) == 4) {
				$gString[] = str_replace("G_", "", $groups[$i]);
							}
			if(substr($groups[$i], 0, 4) == "G_10" && strlen($groups[$i]) == 5) {
				$gString[] = str_replace("G_", "", $groups[$i]);
							}
			if($groups[$i] == "G_Lehrer") {
				return "Lehrer";
			}
			
			if($groups[$i] == "Sek") {
				return "Lehrer";
			}
			
			if($groups[$i] == "Rek") {
				return "Lehrer";
			}
			
			if($groups[$i] == "Konrek") {
				return "Lehrer";
			}
		}
		
		if(sizeof($gString) > 0) return implode(",",$gString);
		else return "Sonstige";
	}
	
	private function encrypt_decrypt($action, $string) {
		$output = false;
	
		$key = 'rgerpgorjgoirmb4949fj49fj49fj';
	
		
		$iv = md5(md5($key));
	
		if( $action == 'encrypt' ) {
			$output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
			$output = base64_encode($output);
		}
		else if( $action == 'decrypt' ){
			$output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
			$output = rtrim($output, "");
		}
		return $output;
	}
	
	
	public static function notifyUserAdded($userID) {
		// Hier werden die Passwörter für ESIS Benutzer erzeugt und per E-Mail verschickt.
		
		$user = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . $userID . "'");
		
		if($user['userNetwork'] == 'ESIS') {

		    // Passwort generieren
			$password = md5(rand() . rand() . $userID);
			$password = strtoupper($password);
			$password = substr($password,0,7);
			
			DB::getDB()->query("UPDATE users SET userCachedPasswordHash='" . login::hash($password) . "',userCachedPasswordHashTime=UNIX_TIMESTAMP(), userLastPasswordChangeRemote=UNIX_TIMESTAMP() WHERE userID='" . $userID. "'");
							
			$email = $user['userName'];
			
			$schueler = explode(", ", $user['userLastName']);
			
			$listSchueler = "";
			
			for($i = 0; $i < sizeof($schueler); $i++) {
				$listSchueler .= "- " . $schueler[$i] . "\r\n";
			}
			
			$mailtext = "";
			eval("\$mailtext = \"" . DB::getTPL()->get("login/emailesis") . "\";");
			
			$mail = new email($email, "Ihr Zugang zu Schule-Intern", $mailtext);
			$mail->send();			
		}
		
	}
	
	public static function getLehrerZugangsdaten() {
		$html = "";
		
		if(DB::checkDemoAccess()) {
			$passwords = DB::getDB()->query("SELECT * FROM lehrer JOIN initialpasswords ON lehrerUserID=initialPasswordUserID ORDER BY RAND(), lehrerName ASC, lehrerRufname ASC LIMIT 10");
			
			while($l = DB::getDB()->fetch_array($passwords)) {
				$html .= "<tr><td>" . $l['lehrerName'] . ", " . $l['lehrerRufname'] . " (" . $l['lehrerKuerzel'] . ")</td>";
				$html .= "<td>" . $l['lehrerKuerzel'] . "</td><td>" . $l['initialPassword'] . "</td>";
				
				$html .= "<td><form><button type=\"button\" class=\"btn\" onclick=\"javascript:simlogin('" . $l['lehrerKuerzel'] . "','" . $l['initialPassword'] . "');\"><i class=\"fa fa-sign-in\"></i></buton></form>";
				
				
				$html .= "</tr>";
			}
			
			return $html;
		}
		else {
			return "";	// Keine Zugangsaten in der Nicht - Demoversion anzeigen
		}
	}
	
	public static function getSchuelerZugangsdaten() {
		$html = "";
		
		if(DB::checkDemoAccess()) {
			$passwords = DB::getDB()->query("SELECT * FROM schueler JOIN initialpasswords ON schuelerUserID=initialPasswordUserID JOIN users ON schuelerUserID=userID ORDER BY RAND(), schuelerName ASC, schuelerRufname ASC LIMIT 10");
			
			while($l = DB::getDB()->fetch_array($passwords)) {
				$html .= "<tr><td>" . $l['schuelerName'] . ", " . $l['schuelerRufname'] . " (Klasse " . $l['schuelerKlasse'] . ")</td>";
				$html .= "<td>" . $l['userName'] . "</td><td>" . $l['initialPassword'] . "</td>";
				
				$html .= "<td><form><button type=\"button\" class=\"btn\" onclick=\"javascript:simlogin('" . $l['userName'] . "','" . $l['initialPassword'] . "');\"><i class=\"fa fa-sign-in\"></i></buton></form>";
				
				
				$html .= "</tr>";
			}
			
			return $html;
		}
		else {
			return "";	// Keine Zugangsaten in der Nicht - Demoversion anzeigen
		}
	}
	
	public static function getElternCodes() {
		$html = "";
		
		if(DB::checkDemoAccess()) {
			$passwords = DB::getDB()->query("SELECT * FROM schueler JOIN eltern_codes ON schuelerAsvID=codeSchuelerAsvID ORDER BY RAND(), schuelerName ASC, schuelerRufname ASC LIMIT 10");
			
			while($l = DB::getDB()->fetch_array($passwords)) {
				$html .= "<tr><td>" . $l['schuelerName'] . ", " . $l['schuelerRufname'] . " (Klasse " . $l['schuelerKlasse'] . ")</td>";
				$html .= "<td>" . $l['codeText'] . "</td>";
				
				$html .= "<td><form><button type=\"button\" class=\"btn\" onclick=\"javascript:window.location.href='index.php?page=elternregister&code=" . $l['codeText'] . "';\"><i class=\"fa fa-sign-in\"></i></buton></form>";
				
				
				$html .= "</tr>";
			}
			
			return $html;
		}
		else {
			return "";	// Keine Zugangsaten in der Nicht - Demoversion anzeigen
		}
	}
	
	public static function notifyUserDeleted($userID) {
		// Offene Sessions löschen
		DB::getDB()->query("DELETE FROM sessions WHERE sessionUserID='" . $userID . "'");
	}
	
	public static function hasSettings() {
		return true;
	}
	
	/**
	 * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
	 * @return array(String, String)
	 * array(
	 * 	   array(
	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
	 *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
	 *      'titel' => "Titel der Beschreibung",
	 *      'text' => "Text der Beschreibung"
	 *     )
	 *     ,
	 *     .
	 *     .
	 *     .
	 *  )
	 */
	public static function getSettingsDescription() {
		return [
		    [
        		  'name' => 'login-loginmessage',
        		  'typ' => 'HTML',
        		  'titel' => 'Nachricht auf dem Loginbildschirm',
        		  'text' => 'Diese Nachricht wird auf dem Login Bildschirm dargestellt. (z.B. Herzlich Willoommen zu ABCintern! Schüler und Lehrer verwenden bitte ihre Schulkennung. Eltern können sich über die E-Mailadresse anmelden.)'
		    ],
            [
                'name' => 'login-background-image',
                'typ' => 'BILD',
                'titel' => 'Hintergrundbild auf dem Loginbildschrim',
                'text' => 'Achten Sie auf möglichst keine Dateigröße. (z.B. JPEG Quality kleiner 50)'
            ]
		];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Benutzerlogin';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
	
	public static function getAdminMenuGroup() {
	    return 'Allgemeine Einstellungen';
	}
	
	public static function getAdminMenuGroupIcon() {
	    return 'fa fa-desktop';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-desktop';
	}

	public static function hasAdmin() {
	    return true;
	}
	
	public static function getAdminGroup() {
	    return 'Webportal_Admin_LoginSettings';
	}
}


?>