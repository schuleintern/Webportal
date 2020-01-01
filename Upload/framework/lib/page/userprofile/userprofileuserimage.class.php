<?php


class userprofileuserimage extends AbstractPage {
	public function __construct() {
		parent::__construct(array("Benutzerprofil", "Benutzerbild"));
	
		$this->checkLogin();
		
		
	}

	public function execute() {
		// Bild vorhanden?
		
	    if(!DB::getSession()->getUser()->userCanChangePassword()) {
	        new errorPage("Kein Zugriff auf das Profil.");
	    }
		
		$userImage = DB::getDB()->query_first("SELECT * FROM image_uploads WHERE uploadUserName LIKE '" . DB::getSession()->getData("userName") . "'");
		$message = "";
		
		if($_GET['deleteImage'] > 0) {
			$upload = new UploadImage($userImage['uploadID']);
			$upload->delete();
			$userImage['uploadID'] = 0;
			
			header("Location: index.php?page=userprofileuserimage");
			exit(0);
		}
		
		if($userImage['uploadID'] > 0) {
			if($_GET['save'] > 0) {
				$upload = new UploadImage($userImage['uploadID']);
				$success = $upload->uploadNewImage("userImage");
				if($success === 2) $message = "Das angegebene Bild ist ungültig (wahrscheinlich ist es kein Bild)";
				elseif($success) $message = "Das Bild wurde erfolgreich hochgeladen.";
			}
			
		
			if($_GET['getImage'] == "fullSize") {
				$upload = new UploadImage($userImage['uploadID']);
				$upload->sendImage();
				exit(0);
			}
			
			if($_GET['getImage'] == "profile") {
				$upload = new UploadImage($userImage['uploadID']);
				$upload->sendImage(500);
				exit(0);
			}
			
			$showImage = "<img src=\"index.php?page=userprofileuserimage&getImage=fullSize\" width=\"200\"><br /><a href=\"index.php?page=userprofileuserimage&deleteImage=1\">Bild löschen</a><br />";
		}
		else {
			if($_GET['save'] > 0) {
				$upload = new UploadImage();
				$success = $upload->uploadNewImage("userImage");
				if($success == 2) $message = "Das angegebene Bild ist ungültig (wahrscheinlich ist es kein Bild)";
				elseif($success) {
					header("Location: index.php?page=userprofileimage");
					exit(0);
				}
			}
			
			$showImge = "Bisher kein Bild<br />";
		}
		
		eval("echo(\"".DB::getTPL()->get("userprofile/userimage")."\");");
		
	
	}
	

	public static function notifyUserDeleted($userID) {
		
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
}


?>