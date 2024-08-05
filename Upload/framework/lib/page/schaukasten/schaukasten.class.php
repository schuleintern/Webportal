<?php

class schaukasten extends AbstractPage {

	private $plan = "";
	
	public function __construct() {
		parent::__construct([],true);
		

	}

	public function execute() {
		$viewKey = $_GET['viewKey'];
		
		$kasten = $_GET['schaukasten'];
		
		
		$plan = DB::getDB()->query_first("SELECT * FROM vplan WHERE vplanName='" . DB::getDB()->escapeString($kasten) . "' AND schaukastenViewKey='" . DB::getDB()->escapeString($viewKey) . "'");
	
		if($plan['vplanName'] != "") {
		    
		    $titelVplan = '';
		    
		    switch($plan['vplanName']) {
		        case 'lehrerheute': $titelVplan = 'Lehrer Heute'; break;
		        case 'lehrermorgen': $titelVplan = 'Lehrer Heute'; break;
		        
		        case 'schuelerheute': $titelVplan = 'Schüler Heute'; break;
		        case 'schuelermorgen': $titelVplan = 'Schüler Morgen'; break;
		        
		    }
		    
			eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schaukasten/displayvplan") . "\");");
			//PAGE::kill(true);
			exit(0);
		}
		else {
			die("Kein Zugriff!");
		}
	
	}
	
	
	public static function getNotifyItems() {
		return array();
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
		return 'Elektr. Schaukasten';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function onlyForSchool() {
		return [];
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-television';
	}
	
	public static function getAdminMenuGroup() {
		return 'Vertretungsplan (deprecated!)';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-retweet';
	}
	
	public static function displayAdministration($selfURL) {
		// Init ViewKeys?
		self::initViewsKeysVPlan();
		
		$vplans = DB::getDB()->query("SELECT * FROM vplan");
		
		$planDATA = "";
		while($plan = DB::getDB()->fetch_array($vplans)) {
			$name = "";
			switch($plan['vplanName']) {
				case 'schuelerheute': $name = "Schüler Heute"; break;
				case 'schuelermorgen': $name = "Schüler Morgen"; break;
				case 'lehrerheute': $name = "Lehrer Heute"; break;
				case 'lehrermorgen': $name = "Lehrer Morgen"; break;
			}
			
			$planDATA .= "<tr><td>" .$name . "</td><td><code>" . DB::getGlobalSettings()->urlToIndexPHP . "?page=schaukasten&schaukasten=" . $plan['vplanName'] . "&viewKey=" . $plan['schaukastenViewKey'] . "</code></td></tR>";
		}
		
		$return = "";
		eval("\$return = \"" . DB::getTPL()->get("schaukasten/admin/index") . "\";");
		
		return $return;
	}
	
	private static function initViewsKeysVPlan() {
		$plans = ['schuelerheute','lehrerheute','schuelermorgen','lehrermorgen'];
		
		for($i = 0; $i < sizeof($plans); $i++) {
			$plan = DB::getDB()->query_first("SELECT * FROM vplan WHERE vplanName='" . $plans[$i] . "'");
			if($plan['schaukastenViewKey'] == '') {
				$newKey = strtoupper(md5(rand()));
				DB::getDB()->query("UPDATE vplan SET schaukastenViewKey='" . $newKey . "' WHERE vplanName='" . $plans[$i] . "'");
			}
		}
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Schaukasten';
	}
}


?>