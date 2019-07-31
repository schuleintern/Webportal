<?php


class editPersons extends AbstractPage {
	private $sections = array(
		
	);
	
	public function __construct() {
		parent::__construct(array("Fotoseite","Personen bearbeiten"));
		
		$this->checkAccessWithGroup("Webportal_Fotoseite");
		
	}

	public function execute() {
		$db = DB::getDB();
		
		if(isset($_GET['action']) && $_GET['action'] == "new") {
			// Neue Person anlegen
			$db->query("INSERT INTO  `rsu_persons` (
							`personID` ,
							`personNachname` ,
							`personVorname` ,
							`personKuerzel` ,
							`personFaecher` ,
							`personFunktion` ,
							`personSectionID` ,
							`personhasPicture`
							)
							VALUES (
								NULL , 
								'{$_POST['new_nachname']}',
								'{$_POST['new_vorname']}',
								'{$_POST['new_kuerzel']}',
								'{$_POST['new_faecher']}',
								'{$_POST['new_funktion']}',
								'{$_POST['new_sectionID']}',
								'0'
							);");
		}
		
		if(isset($_GET['action']) && $_GET['action'] == "edit") {
			// Personen bearbeiten
			$sections = $db->query("SELECT * FROM rsu_sections ORDER BY sectionOrder ASC");
			
			$personsList = "";
			while($s = $db->fetch_array($sections)) {
				$persons = $db->query("SELECT * FROM rsu_persons WHERE personSectionID='" . $s['sectionID'] . "' ORDER BY personNachname ASC, personVorname ASC");
				while($p = $db->fetch_array($persons)) {
					if(isset($_POST["p_{$p['personID']}_delete"]) && $_POST["p_{$p['personID']}_delete"] > 0) {
						// Delete
						$db->query("DELETE FROM rsu_persons WHERE personID='" . $p['personID'] . "'");
					}
					else {
						// Edit
						$db->query("UPDATE rsu_persons SET
								personNachname='" . addslashes($_POST["p_{$p['personID']}_nachname"]) . "',
								personVorname='" . addslashes($_POST["p_{$p['personID']}_vorname"]) . "',
								personKuerzel='" . addslashes($_POST["p_{$p['personID']}_kuerzel"]) . "',
								personFaecher='" . addslashes($_POST["p_{$p['personID']}_faecher"]) . "',
								personFunktion='" . addslashes($_POST["p_{$p['personID']}_funktion"]) . "',
								personSectionID='" . intval($_POST["p_{$p['personID']}_sectionID"]) . "',
                                personIsActive='" . intval($_POST["p_{$p['personID']}_aktiv"]) . "'
							WHERE personID='" . $p['personID'] . "'");
					}
				}
			}
		}
		
		$sections = $db->query("SELECT * FROM rsu_sections ORDER BY sectionOrder ASC");
		
		$personsList = "";
		while($s = $db->fetch_array($sections)) {
			$persons = $db->query("SELECT * FROM rsu_persons WHERE personSectionID='" . $s['sectionID'] . "' ORDER BY personNachname ASC, personVorname ASC");
			while($p = $db->fetch_array($persons)) {
				if($p['personhasPicture'] > 0) {
					$showFotoText = "<img src=\"index.php?page=getPicture&personID=" . $p['personID'] . "\" width=100>";
				}
				else {
					$showFotoText = "<i>Kein Foto</i>";
				}
				
				$select = $this->generateSectionSelect("p_{$p['personID']}_sectionID",$p['personSectionID']);
				
				eval("\$personsList .= \"" . DB::getTPL()->get("rsufotoseite/editPersons_bit") . "\";");
			}
		}
		

		$select = $this->generateSectionSelect("new_sectionID");
		
		eval("echo(\"".DB::getTPL()->get("rsufotoseite/editPersons")."\");");
	}
	
	private function generateSectionSelect($selectName, $sectionID=0) {
		if(sizeof($this->sections) == 0) {
			$all = DB::getDB()->query("SELECT * FROM rsu_sections ORDER BY sectionOrder ASC");
			while($s = DB::getDB()->fetch_array($all)) {
				$this->sections[] = array(
					"id" => $s['sectionID'],
					"name" => $s['sectionName']
				);
			}
		}
		
		$ret = "<select name=\"" . $selectName . "\">";
		
		for($i = 0; $i < sizeof($this->sections); $i++) {
			$ret .= "<option value=\"" . $this->sections[$i]['id'] . "\"" . (($this->sections[$i]['id'] == $sectionID) ? (" selected=\"selected\"") : ("")) . ">" . $this->sections[$i]['name'] ."</option>";
		}
		
		$ret .= "</select>";
		
		return $ret;
		
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
		return 'Lehrerfotoseite';
	}

	public static function hasAdmin() {
	    return true;
	}
	
	public static function getAdminMenuGroup() {
	    return 'Kleinere Module';
	}
	
	public static function getAdminGroup() {
	    return 'Webportal_Fotoseite';
	}
	
	public static function displayAdministration($selfURL) {
	    return 'Administratoren dieser Seite können die Fotoseite bearbeiten. (Globale Administratoren leider nicht.)';
	}
	
	
	/**
	 * Achtung!! Diese Seite nicht für andere Schulen freischalten bevor die MySQL Injections raus sind!
	 * @return string[]
	 */
	public static function onlyForSchool() {
		return array(
			"0740"
		);
	}
}


?>