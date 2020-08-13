<?php


class absenzenstatistik extends AbstractPage {

	private $stundenplan = null;
	private $stundenplanActiveKlasse = null;
	
	private $isSekretariat = false;
	
	public function __construct() {
		
		$this->needLicense = false;
		
		parent::__construct(array("Absenzenverwaltung", "Statistik"));
		
		$this->checkLogin();
	
		$this->isSekretariat = DB::getSession()->isMember("Webportal_Absenzen_Sekretariat");
		
		if(DB::getSession()->isAdmin()) $this->isSekretariat = true;
		
		if(!$this->isSekretariat) die("No Access!");
		
	}

	public function execute() {
		include_once("../framework/lib/data/absenzen/Absenz.class.php");
		include_once("../framework/lib/data/absenzen/AbsenzBefreiung.class.php");
		include_once("../framework/lib/data/absenzen/AbsenzBeurlaubung.class.php");
		include_once("../framework/lib/data/absenzen/AbsenzSchuelerInfo.class.php");
		
		$this->showIndex();
	}
		
	public function showIndex() {
		$schuelerMitAbsenzen = DB::getDB()->query("SELECT DISTINCT absenzSchuelerAsvID FROM absenzen_absenzen");		
		$asvIDs = array();
		
		while($s = DB::getDB()->fetch_array($schuelerMitAbsenzen)) $asvIDs[] = $s['absenzSchuelerAsvID'];
		
		$tableData = "";
		
		for($i = 0; $i < sizeof($asvIDs); $i++) {
			$schueler = schueler::getByAsvID($asvIDs[$i]);
			if($schueler != null) {
				$tableData .= "<tr>";
				$tableData .= "<td>" . $schueler->getCompleteSchuelerName() . " (<a href=\"index.php?page=absenzensekretariat&mode=editAbsenzen&schuelerAsvID=" . $schueler->getAsvID() . "\">Anzeigen</a>)</td>";
				$tableData .= "<td>" . $schueler->getKlasse() . "</td>";
				

				
				$gesamt = 0;
				$urlaub = 0;
				$fpA = 0;
				$absenzen = Absenz::getAbsenzenForSchueler($schueler);
				for($a = 0; $a < sizeof($absenzen); $a++) {
					$gesamt += $absenzen[$a]->getTotalDays();
					$urlaub += $absenzen[$a]->getBeurlaubungTage();
					$fpA += $absenzen[$a]->getTotalDaysNotAnwesend();
				}
				
				$tableData .= "<td>" . $gesamt . "</td>";
				
				if(DB::getSettings()->getBoolean('absenzen-has-fpa')) {
					$tableData .= "<td>" . $fpA . "</td>";
				}
				
				
				$tableData .= "<td>" . $urlaub . "</td>";
				$tableData .= "<td>" . ($gesamt-$urlaub) . "</td>";
				
				$sani = DB::getDB()->query_first("SELECT count(*) AS ANZAHL FROM absenzen_sanizimmer WHERE sanizimmerSchuelerAsvID='" . $schueler->getAsvID() . "'");
				$verspaetungen = DB::getDB()->query_first("SELECT count(*) AS ANZAHL FROM absenzen_verspaetungen WHERE verspaetungSchuelerAsvID='" . $schueler->getAsvID() . "'");
				
				$tableData .= "<td>" . $sani['ANZAHL'] . "</td>";
				$tableData .= "<td>" . $verspaetungen['ANZAHL'] . "</td></tr>";
			}
		}
		
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/statistik/index") . "\");");
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
		return array(
				array(
					'name' => "absenzen-attestnachdreitagen",
					'typ' => BOOLEAN,
					'titel' => "Attest nach 3 Tagen fordern?",
					'text' => "Soll ein Attest nach drei Tagen Abwesenheit gefordert werden?"
				)
		);
	}
	
	
	public static function getSiteDisplayName() {
		return 'Absenzenmodul (Statistik)';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];
	}
	
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Absenzen_Admin';
	}
	
	public static function dependsPage() {
		return ['absenzenberichte','absenzensekretariat'];
	}
	
	
}


?>