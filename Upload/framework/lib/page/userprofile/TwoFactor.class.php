<?php



use OTPHP\TOTP;

/**
 * Funktionen für die Zweifaktorauthentifizierung
 * @author Christian
 *
 */

class TwoFactor extends AbstractPage {
	
	public function __construct() {
				
		parent::__construct ( array (
			"2 - Faktor" 
		) );
		
		$this->checkLogin();
		
		
		if(!self::is2FAActive()) {
		    header("Location: index.php");
		    exit(0);
		}

	}
	
	public function execute() {		
		
	    
	    switch($_REQUEST['action']) {
	        case 'setTOTP':
	            if(!DB::getSession()->getUser()->has2FA()) {
	                sleep(5);
    	            $secret = DB::getSession()->getFromSessionStore("2fasecretinit");
    	            
    	            DB::getSession()->getFromSessionStore("2fasecretinit");
    	            
    	            $new2FAObject = TOTP::create(DB::getSession()->getFromSessionStore("2fasecretinit"));
    	            
    	            $time = time();
    	            
    	            $codeOK = false;
    	            

    	            $code = $_REQUEST['totpCode'];
    	            $code = str_replace("-","",$code);
    	            
    	            if($new2FAObject->verify($code, $time + 30)) $codeOK = true;
    	            if($new2FAObject->verify($code, $time - 30)) $codeOK = true;
    	            if($new2FAObject->verify($code, $time)) $codeOK = true;
    	                	            
    	                	            
    	            if($codeOK) {
    	                DB::getSession()->getUser()->set2FA($new2FAObject->getSecret());
    	                DB::getSession()->removeFromSessionStore("2fasecretinit");
    	                header("Location: index.php?page=TwoFactor");
    	                exit();}
    	            else {
    	                $this->indexPage("Der Code ist falsch. Bitte erneut versuchen. Achten Sie bitte darauf, dass die Uhrzeit Ihres Mobiltelefons richtig eingestellt ist.");
    	            }
	            }
	            else {
	                $this->indexPage("Zweifaktor bereits aktiv.");
	            }
	            
	            
	            
	        break;
	        
	        
	        case 'printTOTPQRCode':
	            if(!DB::getSession()->getUser()->has2FA()) {
	                $secret = DB::getSession()->getFromSessionStore("2fasecretinit");
	                
	                if($secret == "" || $secret == null) new errorPage();
	                
	                $new2FAObject = TOTP::create($secret);
	                
	                $new2FAObject->setLabel(DB::getGlobalSettings()->siteNamePlain);
	                $urlToQRCode = $new2FAObject->getQrCodeUri();
	                
	                eval("\$html = \"" . DB::getTPL()->get("userprofile/2fa/print") . "\";");
	                
	                $print = new PrintNormalPageA4WithHeader("QR Code zur 2FA");
	                $print->setHTMLContent($html);
	                
	                $print->send();             
	                
	                
	                
	            }
	            else {
	                $this->indexPage("Zweifaktor bereits aktiv.");
	            }
	        break;
	        
	        case 'removeTOTP':
	            sleep(5);
	            if(DB::getSession()->getUser()->has2FA()) {
        	        if(DB::getSession()->getUser()->check2FACode($_REQUEST['totpCode'])) {
        	            DB::getSession()->getUser()->set2FA(null);
        	            DB::getSession()->set2FactorActive(false);
        	            header("Location: index.php?page=TwoFactor");
        	            exit();
        	        }
        	        else {
        	            $this->indexPage("Der Code ist falsch. Bitte erneut versuchen. Achten Sie bitte darauf, dass die Uhrzeit Ihres Mobiltelefons richtig eingestellt ist.");
        	        }
	            }
	            else {
	                $this->indexPage("Kein Zweifaktor aktiv.");
	            }
    	    break;
    	    
    	    
    	    
    	    
	        case 'initSession':
	            $this->init2FASession();
	        break;
	        
	        case 'endSession':
	            $this->endSession();
	        break;
	            
	        
	        case 'removeTrustedDevice':
	            $this->removeTrustedDevice();
	        break;
	            
	            
	        default:
	            $this->indexPage();
	        break;
	    }
	    
	}
	
	private function removeTrustedDevice() {
	    DB::getSession()->getUser()->removeCurrentDeviceFromTrustedDevices();
	    DB::getSession()->set2FactorActive(false);
	    
	    header("Location: index.php");
	    exit();
	    
	}
	
	private function endSession() {
	    DB::getSession()->set2FactorActive(false);
	    header("Location: index.php");
	    exit();
	}
	
	private function init2FASession() {
	    $has2FA = DB::getSession()->getUser()->has2FA();
	    
	    
	    
	    
	    $currentPage = "";
	    
	    
	    if(in_array($_REQUEST['gotoPage'], requesthandler::getAllowedActions())) {
	        $currentPage = $_REQUEST['gotoPage'];
	    }
	    else $currentPage = "index";
	    
	    
	    
	    
	    
	    if(!$has2FA) {
	        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("userprofile/2fa/initsession/no2fa") . "\");");
	        PAGE::kill(true);
					//exit(0);
	    }
	    
	    
	    if(DB::getSession()->getUser()->isCurrentDeviceTrusted()) {
	        DB::getSession()->set2FactorActive();
	        header("Location: index.php?page=$currentPage");
	        exit(0);
	    }
	    
	    
	    if($_REQUEST['checkCode'] > 0) {
	    
	        // Prevent Brute Force
	        sleep(2);
	        	        
	        if(DB::getSession()->getUser()->check2FACode($_REQUEST['totpCode'])) {
	            DB::getSession()->set2FactorActive();
	            	            
	            
	            if($_REQUEST['addtotrusteddevices'] > 0 && self::allowTrustedDevices()) {
	                DB::getSession()->getUser()->addCurrentDeviceToTrustedDevices();
	            }
	            
	            
	            
	            header("Location: index.php?page=$currentPage");
	            exit(0);
	        }
	        else {
	            $error = "Code falsch.";
	        }        
	        
	        
	    }
	    
	    if($error != "") $error = "<div class=\"callout callout-danger\">" . $error . "</div>";
	    
	    
	    $userName = DB::getSession()->getUser()->getUserName();
	    $displayName = DB::getSession()->getUser()->getDisplayName();
	    
	    
	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("userprofile/2fa/initsession/index") . "\");");
	    // PAGE::kill(true);
        exit(0);
	    
	    
	    
	    
	}
	
	private function indexPage($showError = null) {
	        
	    
	    $has2FA = DB::getSession()->getUser()->has2FA();
	    
	    
	    if($showError != null) {
	        $error = "<div class=\"callout callout-danger\">" . $showError . "</div>";
	    }
	    else $error = "";
	    
	    
	    if(!$has2FA) {
	        
	        $urlToQRCode = "";
	        
	        if(DB::getSession()->getFromSessionStore("2fasecretinit") != "") {
	            $new2FAObject = TOTP::create(DB::getSession()->getFromSessionStore("2fasecretinit"));
	            
	        }
	        else {
	            $new2FAObject = TOTP::create(null);
	            DB::getSession()->addToSessionStore("2fasecretinit", $new2FAObject->getSecret());
	        }
	        
	        $new2FAObject->setLabel(DB::getGlobalSettings()->siteNamePlain);
	        $urlToQRCode = $new2FAObject->getQrCodeUri();      
	        
	    }

	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("userprofile/2fa/index") . "\");");
	    
		
	}
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getSettingsDescription() {
	    return [
	        [
	           'name' => "2fa-trenner-1",
	           'typ' => 'TRENNER',
	           'titel' => "Allgemeine Einstellungen",
	           'text' => ""
	        ],
	        [
	            'name' => "2fa-active",
	            'typ' => 'BOOLEAN',
	            'titel' => "2 - Faktor Authentifizierung aktivieren",
	            'text' => "2FA Allgemein aktivieren? (Wenn hier deaktiviert, dann sind alle anderen Einstellungen hinfällig.)"
	        ],
	        [
	            'name' => "2fa-allow-trusted-devices",
	            'typ' => 'BOOLEAN',
	            'titel' => "Vertrauenswürdige Geräte aktivieren",
	            'text' => "Bei aktivieter Option kann bei der Eingabe eines Codes die Option \"Code auf diesem Gerät nicht mehr anfordern\" aktiviert werden."
	        ],
	        [
	            'name' => "2fa-trenner-2",
	            'typ' => 'TRENNER',
	            'titel' => "Spezielle Bereiche",
	            'text' => ""
	        ],
	        [
	            'name' => "2fa-active-admin",
	            'typ' => 'BOOLEAN',
	            'titel' => "2FA für die Administration erzwingen?",
	            'text' => ""
	        ],
	        [
	            'name' => "2fa-active-noten",
	            'typ' => 'BOOLEAN',
	            'titel' => "2FA für die Notenverwaltung erzwingen?",
	            'text' => ""
	        ],
	        [
	            'name' => "2fa-trenner-3",
	            'typ' => 'TRENNER',
	            'titel' => "Generell",
	            'text' => ""
	        ],
	        [
	            'name' => "2fa-active-schueler",
	            'typ' => 'BOOLEAN',
	            'titel' => "2FA für Schüler erzwingen?",
	            'text' => ""
	        ],
	        [
	            'name' => "2fa-active-lehrer",
	            'typ' => 'BOOLEAN',
	            'titel' => "2FA für Lehrer erzwingen?",
	            'text' => ""
	        ],
	        [
	            'name' => "2fa-active-eltern",
	            'typ' => 'BOOLEAN',
	            'titel' => "2FA für Eltern erzwingen?",
	            'text' => ""
	        ],
	        [
	            'name' => "2fa-active-sonstige",
	            'typ' => 'BOOLEAN',
	            'titel' => "2FA für Sonstige Benutzer erzwingen?",
	            'text' => ""
	        ],
	    ];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Zweifaktor';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	}
	
	public static function hasAdmin() {
		return true;
	}

	public static function getAdminMenuGroup() {
		return "Benutzerverwaltung";
	}

	public static function getAdminMenuGroupIcon() {
	    return 'fa fa-users';
	}
	
	public static function getAdminMenuIcon() {
	    return 'fa fa-key';
	}
	
	public static function dependsPage() {
		return [];
	}
	
	public static function userHasAccess($user) {
	
		return true;
	}
		
	public static function siteIsAlwaysActive() {
	    return true;
	}

	public static function displayAdministration($selfURL) {
	    switch($_REQUEST['action']) {
	        default:
	            return self::showIndex($selfURL);
	        break;
	        
	        case 'Remove2FA':
	            return self::remove2FA($selfURL);
	        break;
	    }
	}
	
	private static function remove2FA($selfURL) {
	    $user = user::getUserByID($_REQUEST['userID']);
	    
	    if($user != null) {
	        if($user->has2FA()) {
	            $user->set2FA("");
	        }	        
	    }
	    
	    header("Location: $selfURL");
	    exit(0);
	    
	}
	
	private static function showIndex($selfURL) {
	    $users = user::getAll();
	    
	    $usersWith2FA = [];
	    
	    for($i = 0; $i < sizeof($users); $i++) {
	        if($users[$i]->has2FA()){
	            $usersWith2FA[] = $users[$i];
	        }
	    }
	    
	      
	    
	    $tableHTML = "";
	    
	    for($i = 0; $i < sizeof($usersWith2FA); $i++) {
	        $tableHTML .= "<tr><td>" . $usersWith2FA[$i]->getUserName() . "</td>";
	        $tableHTML .= "<td><a href=\"" . $selfURL . "&action=Remove2FA&userID=" . $usersWith2FA[$i]->getUserID() . "\" class=\"btn btn-danger btn-sm\"><i class=\"fa fa-trash\"></i> 2FA entfernen</a></td></tr>";
	    }
	    
	    
	    $html = "";
	    
	    eval("\$html .= \"" . DB::getTPL()->get("userprofile/2fa/admin/index") . "\";");
	    return $html;
	    
	    
	    
	}
	
	
	
	// Allgemeine Warapper für Einstellungen
	
	/**
	 * Ist 2FA für einen bestimmten Nutzer generell aktiv?
	 * @param unknown $user
	 */
	public static function TwoFactorForUser(user $user) {
	    return $user->is2FAActive();
	}
	
	/**
	 * Ist 2 Fatktor generell aktiv?
	 * @return boolean
	 */
	public static function is2FAActive() {
	    return DB::getSettings()->getBoolean('2fa-active');
	}
	
	/**
	 * Erlaubten ein Gerät für den Nutzer zu einem Gerät ohne erneute Nachfrage zu machen.
	 * @return boolean
	 */
	public static function allowTrustedDevices() {
	    return DB::getSettings()->getBoolean('2fa-allow-trusted-devices');
	}
	
	public static function force2FAForAdmin() {
	    return DB::getSettings()->getBoolean('2fa-active-admin');
	}
	
	public static function force2FAForNoten() {
	    return DB::getSettings()->getBoolean('2fa-active-noten');
	}
	
	public static function enforcedForUser(user $user) {
	    if($user->isPupil() && DB::getSettings()->getBoolean('2fa-active-schueler')) {
	        return true;
	    }

	    else if($user->isTeacher() && DB::getSettings()->getBoolean('2fa-active-lehrer')) {
	        return true;
	    }
	    
	    else if($user->isEltern() && DB::getSettings()->getBoolean('2fa-active-eltern')) {
	        return true;
	    }
	    
	    else if(DB::getSettings()->getBoolean('2fa-active-sonstige')) {
	        return true;
	    }
	    else return false;
	}

}

?>