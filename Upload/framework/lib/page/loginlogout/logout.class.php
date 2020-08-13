<?php



class logout extends AbstractPage {
	public function __construct() {
		parent::__construct("Startseite");
		
		if(DB::isloggedin()) {
			DB::getSession()->delete();
		}
	}

	public function execute() {
		$message = "<div class=\"callout callout-info\"><p><strong>Sie wurden erfolgreich abgemeldet.</strong></p></div>";
		
		if(DB::getSettings()->getValue("general-wartungsmodus")) {
			eval("echo(\"".DB::getTPL()->get("wartungsmodus/index")."\");");
		}
		else {
			eval("echo(\"".DB::getTPL()->get("login/index")."\");");
		}
		
			
		exit();
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