<?php



/**
 * Rücksetzung eines vergessenen Passwortes.
 * TODO: Neu machen!
 * TODO: Neues Passwort nach Klick auf Link vergeben!
 * @author Christian
 *
 */
class forgotPassword extends AbstractPage {
	public function __construct() {
		parent::__construct(array("Startseite"));	
	}

	public function execute() {
		
		if(isset($_GET['action'])) {
			// Mail suchen
			if($_GET['action'] == 'step1') {
		

				$esis = DB::getDB()->query_first("SELECT * FROM users WHERE userName LIKE '" . DB::getDB()->escapeString(trim($_POST['email'])) . "' ");
				

				if($esis['userCanChangePassword'] == 0) {
				    echo(json_encode(['success' => false]));
				    exit(0);
				}
				
				if($esis['userID'] > 0) {
					// Erzeuge Passwort vergessen Link
					$found = true;
					$this->createMail($esis['userID'], $esis['userEMail'] );
					echo(json_encode(['success' => true]));
					exit(0);
				}
				
				echo(json_encode(['success' => false]));
				exit(0);
				
			}
			
			if($_GET['action'] == 'step2') {
			    
			    $openForgotPWStep2 = true;
			    
			    eval("echo(\"".DB::getTPL()->get("login/index")."\");");
			    
			    exit();
			}
			
			if($_GET['action'] == 'step3') {
			    
			    $answer = [
			        'success' => false
			    ];
			    
				if(isset($_REQUEST['code'])) {
				    $reset = DB::getDB()->query_first("SELECT * FROM resetpassword WHERE resetCode='" . DB::getDB()->escapeString($_REQUEST['code']) . "'");
					if($reset['resetID'] > 0) {
					    
					       if(strlen($_REQUEST['password1']) > 5) {
					    
    							$user = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . $reset['resetUserID'] . "'");
    							
    							DB::getDB()->query("UPDATE users SET userCachedPasswordHash='" . login::hash($_REQUEST['password1']) . "' WHERE userID='" . $reset['resetUserID'] . "'");
    														
    							DB::getDB()->query("DELETE FROM resetpassword WHERE resetCode='" . DB::getDB()->escapeString($_GET['code']) . "'");
    							
    							nextcloud::updatePasswordForCurrentUser($_REQUEST['password1']);
    								
    							$answer['success'] = true;
							
					       }
					}
				}
				
				echo (json_encode($answer));
				exit(0);

			}
		
		}
		else {
			// Formular zeigen
			eval("echo(\"" . DB::getTPL()->get("forgotPassword/index") . "\");");
		}
		
	}
	private function createMail($userID, $mail) {
		// Alle Anfragen löschen
		DB::getDB ()->query ( "DELETE FROM resetpassword WHERE resetUserID='" . $userID . "'" );
		$passwordHash = "";
		$passwordCode = strtoupper ( md5 ( rand () ) . md5 ( rand () ) );
		
		DB::getDB ()->query ( "INSERT INTO resetpassword (resetUserID, resetNewPasswordHash, resetCode) values
						(
						'" . $userID . "',
						'" . $passwordHash . "',
						'" . $passwordCode . "'
						)
				" );
				
		$mailText = "";
		
		eval ( "\$mailText = \"" . DB::getTPL ()->get ( "forgotPassword/mailStep1" ) . "\";" );
		
		$mailObject = new email ( $mail, "Link zum Passwortzurücksetzen bei " . DB::getGlobalSettings()->siteNamePlain, $mailText );
		$mailObject->isHTML();
		
		$mailObject->sendInstantMail();
	}
	
	public static function notifyUserDeleted($userID) {
		DB::getDB()->query("DELETE FROM resetpassword WHERE resetUserID='" . $userID . "'");
	}
	
	public static function hasSettings() {
		return false;
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
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Passwort vergessen';
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