<?php

/**
 * 
 * @author Christian
 */
class userprofilepassword extends AbstractPage {
	public function __construct() {
		parent::__construct(array("Benutzerprofil", "Passwort ändern"));
		
		$this->checkLogin();
		
		if(DB::getSession()->isSyncedUser()) {
			new errorPage("Die Passwortänderung ist nur noch für lokal erstellte Benutzer möglich!");
		}
		
		if(DB::getGlobalSettings()->schulnummer == "9400") {
			new errorPage("In der Demo Version ist keine Passwort Änderung möglich!");
		}
		
		if(DB::getSession()->getUser()->has2FA() && !DB::getSession()->is2FactorActive()) {
		    header("Location: index.php?page=TwoFactor&action=initSession&gotoPage=userprofilepassword");
		    exit(0);
		}
	}

	public function execute() {
	
	    if(!DB::getSession()->getUser()->userCanChangePassword()) {
	        new errorPage("Kein Zugriff auf das Profil.");
	    }
		
		
		switch($_GET['action']) {
			default:
					if($_GET['tooShort'] > 0) {
						$message = "<font color=red>Das angegebene Passwort ist zu kurz. Es muss mindestens 6 Stellen haben.</font>";
					}
					
					elseif($_GET['noMatch'] > 0) {
						$message = "<font color=red>Die beiden Passwörter stimmen nicht überein!</font>";
					}
					
					elseif($_GET['changed'] > 0)  {
						$message = "<font color=green>Das Passwort wurde erfolgreich geändert.</font>";
					}
					
					elseif($_GET['failed'] > 0)  {
						$message = "<font color=red>Das Passwort konnte wegen eines internen Fehlers nicht geändert werden. Bitte später erneut versuchen.</font>";
					}
					
					elseif($_GET['oldpwnotok'] > 0)  {
						$message = "<font color=red>Das alte Passwort ist nicht korrekt.</font>";
					}
					
					elseif(DB::getSession()->getData("hasToChangePassword") > 0) {
						$message = "<font color=red>Das Passwort muss für diesen Benutzer geändert werden. (Meistens bei der ersten Anmeldung oder nach Zurücksetzen des Passwortes.)</font>";
					}
					eval("echo(\"".DB::getTPL()->get("userprofile/changepassword")."\");");
					PAGE::kill();
					//exit(0);
				
			break;
			
			case "changePassword":
				$newPassword = $_POST['pw1'];
				

				if(!login::check_password(DB::getSession()->getData("userCachedPasswordHash"), $_POST['oldpw'])) {
					header("Location: index.php?page=userprofilepassword&gplsession={$_GET['gplsession']}&oldpwnotok=1");
					exit(0);
				}
				
				if($_POST['pw1'] != $_POST['pw2']) {
					header("Location: index.php?page=userprofilepassword&noMatch=1");
					exit(0);
				}
				
				if(strlen($newPassword) < 6) {
					header("Location: index.php?page=userprofilepassword&tooShort=1");
					exit(0);
				}
				else {
					DB::getDB()->query("UPDATE users SET userCachedPasswordHash='" . login::hash($newPassword) . "',userCachedPasswordHashTime=UNIX_TIMESTAMP() WHERE userID='" . DB::getSession()->getData("userID") . "'");
						
					
					nextcloud::updatePasswordForCurrentUser($newPassword);
					
					
					header("Location: index.php?page=userprofilepassword&changed=1");
					exit(0);

				}
			break;
		}
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
		return '';
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
	
	/**
	 * 
	 * @param user $user
	 */
	public static function userHasAccess($user) {
		if($user->getData('userRemoteUserID') != '') return false;		// Synced User kein Zugriff auf Passwort ändern.
		return true;
	}
}


?>