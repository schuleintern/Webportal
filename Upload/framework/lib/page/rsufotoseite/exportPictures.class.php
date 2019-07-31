<?php


class exportPictures extends AbstractPage {
	private $sections = array(
		
	);
	
	public function __construct() {
		parent::__construct(array("Fotoseite", "Bilder exportieren"));
		
		$this->checkAccessWithGroup("Webportal_Fotoseite");
	}

	public function execute() {
				
		$db = DB::getDB();
		
		$sections = $db->query("SELECT * FROM rsu_sections ORDER BY sectionOrder ASC");
		
		$foldername = md5(rand());
		
		// mkdir("tempzip/" . $foldername);
		
		$zip = new ZipArchive();
		$filename = "tempzip/$foldername.zip";
		
		if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
		    die("cannot open <$filename>\n");
		}
		
		
		$personsList = "";
		while($s = $db->fetch_array($sections)) {
			$persons = $db->query("SELECT * FROM rsu_persons WHERE personSectionID='" . $s['sectionID'] . "' ORDER BY personNachname ASC, personVorname ASC");
			while($p = $db->fetch_array($persons)) {
				if($p['personhasPicture'] > 0 && file_exists("rsufotoseite/" . $p['personID'] . ".jpg")) {
					$zip->addFile("rsufotoseite/" . $p['personID'] . ".jpg", ($p['personNachname'] . ", " . ($p['personVorname']) . ".jpg"));
				}
			}
		}
		
		$zip->close();
				
		// Send File
		
		$file = $filename;
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename='.basename("Bilder Personen an der RSU.zip"));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		flush();
		readfile($file);
		
		// unlink($file);
		exit(0);
		
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
		return 'Fotoseite: Bilder als ZIP exportieren';
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