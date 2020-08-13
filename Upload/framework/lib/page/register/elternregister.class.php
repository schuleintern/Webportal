<?php

class elternregister extends AbstractPage {
	public function __construct() {
		parent::__construct("Startseite");
		
		if(DB::isloggedin()) {
			header("Location: index.php");
			exit;
		}
	}

	public function execute() {
		switch($_GET['mode']) {
			default:
				$this->showIndex();
			break;
			
			case 'checkRegisterCode':
			    $this->checkRegisterCode();
			
			case "step1":
				$this->step1();				
			break;
			
			case "step2":
				$this->step2();
			break;
		}
	}
	
	private function checkRegisterCode() {
	    
	    sleep(2);
	    
	    $code = DB::getDB()->query_first("SELECT * FROM eltern_codes WHERE codeText='" . DB::getDB()->escapeString($_POST['code']) . "'");
	    
	    $result = [
	        'codeOK' => false,
	        'schuelerVorname' => '',
	        'schuelerName' => '',
	        'schuelerKlasse' => ''
	    ];
	    
	    if($code['codeID'] > 0) {
	        $schueler = schueler::getByAsvID($code['codeSchuelerAsvID']);
	        
	        if($schueler != null) {
	            $result = [
	                'codeOK' => true,
	                'schuelerVorname' => $schueler->getRufname(),
	                'schuelerName' => $schueler->getName(),
	                'schuelerKlasse' => $schueler->getKlasse()
	            ];
	        }
	    }
	    
	    echo(json_encode($result));
	    exit(0);
	}
	
	private function step2() {
		$register = DB::getDB()->query_first("SELECT * FROM eltern_register WHERE registerID='" . intval($_GET['registerID']) ."' AND registerCheckKey='" . DB::getDB()->escapeString($_GET['code']) . "'");
		if($register['registerID'] > 0) {
		    
		    // Check if User exists
		    
		    $user = user::getByUsername(trim($register['registerMail']));
		    
		    if($user != null) {
		        $usernameTaken = true;
		        eval("DB::getTPL()->out(\"".DB::getTPL()->get("elternregister/step3")."\");");
		        
		    }
		    else {
    		    
    		    
    		    
    		    // Benutzer erstellen
    			DB::getDB()->query("INSERT INTO users
    				(
    					userName,
    					userFirstName,
    					userLastName,
    					userCachedPasswordHash,
    					userCachedPasswordHashTime,
    					userNetwork,
    					userEMail
    				)
    					values(
    					'" . DB::getDB()->escapeString($register['registerMail']) . "',
    					'" . DB::getDB()->escapeString($register['firstName']) . "',
    					'" . DB::getDB()->escapeString($register['lastName']) . "',
    					'" . $register['registerPassword'] . "',
    					UNIX_TIMESTAMP(),
    					'SCHULEINTERN_ELTERN',
    					'" . DB::getDB()->escapeString($register['registerMail']) . "')
    					");
    			$newID = DB::getDB()->insert_id();
    			
    			$code = DB::getDB()->query_first("SELECT * FROM eltern_codes WHERE codeText='" . $register['registerSchuelerKey'] . "'");
    			
    			DB::getDB()->query("INSERT INTO eltern_email (elternEMail,elternSchuelerAsvID,elternUserID) values('" . DB::getDB()->escapeString($register['registerMail']) . "','" . DB::getDB()->escapeString($code['codeSchuelerAsvID']) . "','" . $newID . "')");
    			
    			if($code['codeUserID'] == 0 || $code['codeUserID'] == "") {
    				DB::getDB()->query("UPDATE eltern_codes SET codeUserID='" . $newID . "' WHERE codeID='" . $code['codeID'] . "'");
    			}
    			else {
    				DB::getDB()->query("UPDATE eltern_codes SET codeUserID='" . $code['codeUserID'] . "," . $newID . "' WHERE codeID='" . $code['codeID'] . "'");
    			}
    			
    			DB::getDB()->query("DELETE FROM eltern_register WHERE registerID='" . $register['registerID'] . "'");
    			
    			
    			eval("DB::getTPL()->out(\"".DB::getTPL()->get("elternregister/step3")."\");");
		    }
		}
		else {
			die("Der angegebene Link ist nicht gültig!");
		}
	}
	
	private function step1() {
		
	    $error = false;
	    $mailAlreadyRegistered = false;
	    
		// Vorname angegeben?
		if(trim($_POST['vorname']) == "") {
			$error = true;
		}
		
		
		// Nachname angegeben?
		if(trim($_POST['nachname']) == "") {
		    $error = true;
		}
				
		// E-Mailadresse gültig?
		if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
		    $error = true;
		}
		
		// Registriert?
		$reg = DB::getDB()->query_first("SELECT * FROM eltern_email WHERE elternEMail LIKE '" . DB::getDB()->escapeString($_POST['email']) . "'");
		if($reg['elternSchuelerAsvID'] != "") {
		    $error = true;
		    $mailAlreadyRegistered = true;
		}
		
		// Code richtig?
		$code = DB::getDB()->query_first("SELECT * FROM eltern_codes WHERE codeText='" . DB::getDB()->escapeString($_POST['code']) . "'");
		if(!($code['codeID'] > 0)) {
			$error = true;
		}
		
		// Passwörter korrekt?
		
		if(strlen($_POST['password1']) < 6) {
		    $error = true;
		    
		}
		
		if($_POST['password1'] != $_POST['password2']) {
		    $error = true;
		    
		}
		
		// Registrieren
		
		if(DB::getGlobalSettings()->schulnummer == "9400") {
			// Demoversion --> direkt registrieren
			
			DB::getDB()->query("INSERT INTO users
				(
					userName,
					userFirstName,
					userLastName,
					userCachedPasswordHash,
					userCachedPasswordHashTime,
					userNetwork,
					userEMail
				)
					values(
					'" . DB::getDB()->escapeString($_POST['email']) . "',
					'" . DB::getDB()->escapeString($_POST['vorname']) . "',
					'" . DB::getDB()->escapeString($_POST['nachname']) . "',
					'" . login::hash($_POST['password1']) . "',
					UNIX_TIMESTAMP(),
					'SCHULEINTERN_ELTERN',
					'" . DB::getDB()->escapeString($_POST['email']) . "')
					");
			$newID = DB::getDB()->insert_id();
			
			$code = DB::getDB()->query_first("SELECT * FROM eltern_codes WHERE codeText='" . $_POST['code'] . "'");
			
			DB::getDB()->query("INSERT INTO eltern_email (elternEMail,elternSchuelerAsvID,elternUserID) values('" . DB::getDB()->escapeString($_POST['email']) . "','" . DB::getDB()->escapeString($code['codeSchuelerAsvID']) . "','" . $newID . "')");
			
			if($code['codeUserID'] == 0 || $code['codeUserID'] == "") {
				DB::getDB()->query("UPDATE eltern_codes SET codeUserID='" . $newID . "' WHERE codeID='" . $code['codeID'] . "'");
			}
			else {
				DB::getDB()->query("UPDATE eltern_codes SET codeUserID='" . $code['codeUserID'] . "," . $newID . "' WHERE codeID='" . $code['codeID'] . "'");
			}
						
			$answer = [
			    'success' => true,
			    'isDemo' => true
			];
			
			echo(json_encode($answer));
			exit(0);
		}
		else {
		    
		    if($error) {
		        
		        $answer = [
		            'success' => false,
		            'mailAlreadyRegistered' => $mailAlreadyRegistered
		        ];
		        
		        echo(json_encode($answer));
		        exit(0);
		    }
		    else {
    
    		
    			$code = md5(rand());
    			
    			DB::getDB()->query("INSERT INTO eltern_register (firstName, lastName, registerCheckKey, registerSchuelerKey, registerTime, registerPassword, registerMail) values('" . DB::getDB()->escapeString($_POST['vorname']) . "','" . DB::getDB()->escapeString($_POST['nachname']) . "','" . $code . "','" . DB::getDB()->escapeString($_POST['code']) . "',UNIX_TIMESTAMP(),'" . login::hash($_POST['password1']) . "','" . DB::getDB()->escapeString(strtolower($_POST['email'])) . "')");
    			
    			$newID = DB::getDB()->insert_id();
    			
    			$text = DB::getSettings()->getValue("elternregister-mailtext");
    			$text = str_replace("{LINK}", DB::getGlobalSettings()->urlToIndexPHP . "?page=elternregister&mode=step2&registerID=" . $newID . "&code=" . $code, $text);
    			$text = ($text);
    			
    			$registerLink = DB::getGlobalSettings()->urlToIndexPHP . "?page=elternregister&amp;mode=step2&amp;registerID=" . $newID . "&amp;code=" . $code;
    			
    			eval("\$mailHTML = \"" . DB::getTPL()->get("elternregister/registerMail") . "\";");
    			
    			$mail = new email($_POST['email'], "Ihre Registrierung bei " . DB::getGlobalSettings()->siteNamePlain, $mailHTML);
    			$mail->isHTML();
    			$mail->sendInstantMail();
    			
    			
    			$answer = [
    			    'success' => true
    			];
    			
    			echo(json_encode($answer));
    			exit(0);
    			
		    }
		}
	}
	
	private function showIndex($message = "") {
		if($message != "") $message = "<div class=\"callout callout-danger\">" . $message . "</div>";
		
				
		eval("DB::getTPL()->out(\"".DB::getTPL()->get("elternregister/index")."\");");
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
		return [];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Elternregistrierung';
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
	
}


?>