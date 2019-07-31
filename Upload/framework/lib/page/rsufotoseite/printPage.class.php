<?php


class printPage extends AbstractPage {
	private $sections = array(
		
	);
	
	public function __construct() {
		parent::__construct(array("Fotoseite", "Drucken")); // Zeigt eigenes Layout
		
		$this->checkLogin();
	}

	public function execute() {
		header("Content-type: text/html; charset=utf-8");
		
		$db = DB::getDB();
		
		$message = "";
		
		if(isset($_GET['print']) && $_GET['print'] > 0) {
			$settings = $db->query_first("SELECT * FROM rsu_print");

			$sections = $db->query("SELECT * FROM rsu_sections ORDER BY sectionOrder ASC");
			
			$showHeading = false;
			
			$content = "";

			
			$width = round(100 / $settings['printPerRow']);
			
			while($s = $db->fetch_array($sections)) {
				if($showHeading) {		// Erste Sektion keine Überschrift
					$s['sectionName'] = ($s['sectionName']);
					$heading = "<font color=navy size=+2>" . $s['sectionName'] . "</font>";
					$sectionHTML = "";
				}
				else {
					$heading = "";
					$showHeading = true;
					
					eval("\$sectionHTML = \"" . DB::getTPL()->get("rsufotoseite/print/print_first_col") . "\";");
				}
				
				$persons = $db->query("SELECT * FROM rsu_persons WHERE personSectionID='" . $s['sectionID'] . "' AND personIsActive=1 ORDER BY personNachname, personVorname, personKuerzel");
				
				$row = 0;
				if($showHeading) $row++;
				while($p = $db->fetch_array($persons)) {
					$row++;
					if($p['personhasPicture'] > 0) $foto = "<img src=\"?page=getPicture&personID=" . $p['personID'] . "\" height=\"85\">";
					else $foto = "<p align=\"center\"><i>Bisher kein Foto</i></p>";
					
					if($p['personNachname'] == "zz_a_blank") {
						$p['personNachname'] = "";
						$foto = "&nbsp;";
					}
					
					$p['personNachname'] = ($p['personNachname']);
					$p['personVorname'] = ($p['personVorname']);
					$p['personKuerzel'] = ($p['personKuerzel']);
					
					if(substr($p['personNachname'],0,5) == "zz_a_") $p['personNachname'] = substr($p['personNachname'],5);
					
					if(substr($p['personNachname'],0,3) == "zz_") $p['personNachname'] = substr($p['personNachname'],3);
					
					$width = floor(100 / $settings['printPerRow']);
					
					eval("\$sectionHTML .= \"" . DB::getTPL()->get("rsufotoseite/print/print_col") . "\";");
					
					if($row == $settings['printPerRow']) {
						$sectionHTML .= "</tr><tr>";
						$row = 0;
					}
				}
				
				$date = date("d.m.Y");
				
				if(!$showHeading) {
					$styleTable = "page-break-after: always; table-layout:fixed";
				}
				else $styleTable = "table-layout:fixed";
				
				eval("\$content .= \"" . DB::getTPL()->get("rsufotoseite/print/print_section") . "\";");
			}
			
			eval("echo(\"" . DB::getTPL()->get("rsufotoseite/print/print") . "\");");
			exit(0);
		}
		
		eval("echo(\"" . DB::getTPL()->get("rsufotoseite/print/index") . "\");");
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
		return 'Fotoseite: Seite drucken';
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