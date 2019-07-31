<?php


class editSections extends AbstractPage {
	public function __construct() {
		parent::__construct(array("Fotoseite", "Sektionen bearbeiten"));
		
		$this->checkAccessWithGroup("Webportal_Fotoseite");
	}

	public function execute() {
		
		$db = DB::getDB();
		
		if(isset($_GET['action']) && $_GET['action'] == "edit") {
			// Speichern
			$sections = $db->query("SELECT * FROM rsu_sections ORDER BY sectionOrder");
			
			$htmlSections = "";
			
			while($s = $db->fetch_array($sections)) {
				// Löschen?
				if(isset($_POST['s_' . $s['sectionID'] . '_delete']) && $_POST['s_' . $s['sectionID'] . '_delete'] > 0) {
					$db->query("DELETE FROM rsu_sections WHERE sectionID='" . $s['sectionID'] . "'");
					$db->query("DELETE FROM rsu_persons WHERE personSectionID='" . $s['sectionID']."'");
				}
				else  {
					$db->query("UPDATE rsu_sections SET sectionName='" . addslashes($_POST['s_' . $s['sectionID'] . '_name']) . "',
							sectionOrder='" . intval($_POST['s_' . $s['sectionID'] . '_order']) . "' WHERE sectionID='" . $s['sectionID'] . "'");
				}
			}
		}
		elseif(isset($_GET['action']) && $_GET['action'] == "new") {
			$db->query("INSERT INTO rsu_sections (sectionName, sectionOrder) values('" . addslashes($_POST['new_name']) . "', '" . intval($_POST['new_order']) . "')");
		}
		
		$sections = $db->query("SELECT * FROM rsu_sections ORDER BY sectionOrder");
		
		$htmlSections = "";
		
		while($s = $db->fetch_array($sections)) {
			eval("\$htmlSections .= \"" . DB::getTPL()->get("rsufotoseite/editSectionsBit") . "\";");
		}
		
		eval("echo(\"".DB::getTPL()->get("rsufotoseite/editSections")."\");");
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
		return 'Fotoseite: Sektionen bearbeiten';
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