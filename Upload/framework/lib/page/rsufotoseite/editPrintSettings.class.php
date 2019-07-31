<?php


class editPrintSettings extends AbstractPage {
	private $sections = array(
		
	);
	
	public function __construct() {
		parent::__construct(array("Fotoseite", "Druckeinstellungen bearbeiten"));
		
		$this->checkAccessWithGroup("Webportal_Fotoseite");
	}

	public function execute() {
		$db = DB::getDB();
		
		$message = "";
		
		if(isset($_GET['save']) && $_GET['save'] > 0) {
			$db->query("UPDATE rsu_print SET
					printHeading='" . addslashes($_POST['printHeading']) . "',
					printPerRow='" . intval($_POST['printPerRow']) . "'");
			
			$message = "<font color=green>Einstellungen wurden gespeichert!</font>";
		}
		
		$settings = $db->query_first("SELECT * FROM rsu_print");
		
		eval("echo(\"" . DB::getTPL()->get("rsufotoseite/editPrintSettings") . "\");");
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
		return 'Fotoseite: Druckeinstellungen bearbeiten';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function onlyForSchool() {
		return array(
				"0740"
		);
	}
}


?>