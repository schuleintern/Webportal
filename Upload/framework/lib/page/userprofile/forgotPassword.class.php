<?php



/**
 * Rücksetzung eines vergessenen Passwortes.
 * TODO: Neu machen!
 * TODO: Neues Passwort nach Klick auf Link vergeben!
 * @author Christian
 *
 * Done: Fehlermeldungen im Frontend ausgeben
 * Done: E-Mail oder Benutzername
 * @author: Christian Marienfeld  
 */
class forgotPassword extends AbstractPage {
	public function __construct() {
		parent::__construct(array("Startseite"));	
	}

	public function execute() {
		
		if(isset($_GET['action'])) {

			// Mail suchen
			if($_GET['action'] == 'step1') {
		
				$postUser = DB::getDB()->escapeString(trim($_POST['email']));
				if(!$postUser) {
					echo(json_encode(['success' => false, 'msg' => 'Bitte geben sie einen Benutzer oder E-Mail Adresse sein.']));
					exit(0);
				}

				$esis = DB::getDB()->query_first("SELECT * FROM users WHERE userName LIKE '" . $postUser . "' ");
				
				if(!$esis['userID']) {
					$esis = DB::getDB()->query_first("SELECT * FROM users WHERE userEMail LIKE '" . $postUser . "' ");
				}

				if(!$esis['userID']) {
					echo(json_encode(['success' => false, 'msg' => 'Es wurde kein passender Benutzer gefunden.']));
					exit(0);
				}

				if($esis['userCanChangePassword'] == 0) {
				    echo(json_encode(['success' => false, 'msg' => 'Sie haben kein Recht das Passwort zu verändern.']));
				    exit(0);
				}
				
				if(isset($esis['userID']) && $esis['userID'] > 0) {
					// Erzeuge Passwort vergessen Link
					$this->createMail($esis['userID'], $esis['userEMail'] );
					echo(json_encode(['success' => true, 'msg' => 'Es wurde eine E-Mail an die betreffende Adresse verschickt.']));
					exit(0);
				}
				
				echo(json_encode(['success' => false, 'msg' => 'Leider ist ein Fehler aufgetreten.']));
				exit(0);
				
			}
			
			if($_GET['action'] == 'step2') {
			    
			    $openForgotPWStep2 = true;
			    
			    eval("echo(\"".DB::getTPL()->get("login/index")."\");");
			    
			    PAGE::kill(true);
      		//exit(0);
			}
			
			if($_GET['action'] == 'step3') {
			    	
				if(strlen($_REQUEST['password1']) <= 5) {
					echo(json_encode(['success' => false, 'msg' => 'Das Passwort muss mindestens 6 Zeichen beinhalten.']));
				  exit(0);
				}

				if(isset($_REQUEST['code'])) {
				  $reset = DB::getDB()->query_first("SELECT * FROM resetpassword WHERE resetCode='" . DB::getDB()->escapeString($_REQUEST['code']) . "'");
					
					if($reset['resetID'] > 0) {
					
							$user = DB::getDB()->query_first("SELECT * FROM users WHERE userID='" . $reset['resetUserID'] . "'");
							DB::getDB()->query("UPDATE users SET userCachedPasswordHash='" . login::hash($_REQUEST['password1']) . "' WHERE userID='" . $reset['resetUserID'] . "'");
							DB::getDB()->query("DELETE FROM resetpassword WHERE resetCode='" . DB::getDB()->escapeString($_GET['code']) . "'");
							nextcloud::updatePasswordForCurrentUser($_REQUEST['password1']);
					
							echo(json_encode(['success' => true, 'msg' => 'Das Passwort wurde erfolgreich geändert.']));
							exit(0);

					}
				}
				
				echo(json_encode(['success' => false, 'msg' => 'Leider ist ein Fehler aufgetreten.']));
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