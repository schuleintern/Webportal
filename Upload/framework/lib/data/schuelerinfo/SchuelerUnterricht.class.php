<?php


class SchuelerUnterricht {
	/**
	 * 
	 * @var unknown
	 */
	private $data;
	
	/**
	 * Klassen, die in diesem Unterricht unterrichtet werden.
	 * @var klasse[]
	 */
	private $klassen = [];
	
	private $koppelUnterricht = [];
	
	private $schueler = [];
	
	public function __construct($data, $isKoppelUnterricht=false) {
		$this->data = $data;
		

		$unterrichtIDsSelbeLehrer = [$this->data['unterrichtID']];
		
		if($this->data['unterrichtKoppelText'] != '' && !$isKoppelUnterricht) {
			$daten = DB::getDB()->query("SELECT * FROM unterricht WHERE unterrichtKoppelText='" . $this->data['unterrichtKoppelText'] . "' AND unterrichtID!='" . $this->data['unterrichtID'] . "'");
			
			$this->koppelUnterricht = [];
			
			while($u = DB::getDB()->fetch_array($daten)) {
				$this->koppelUnterricht[] = new SchuelerUnterricht($u, true);
				if($u['unterrichtLehrerID'] == $this->data['unterrichtLehrerID']) {
					$unterrichtIDsSelbeLehrer[] = $u['unterrichtID'];
				}
			}
			
		}
		
		// Schüler zusammenstellen
		
		$daten = DB::getDB()->query("SELECT * FROM unterricht_besuch NATURAL JOIN schueler WHERE unterrichtID IN ('" . implode("','",$unterrichtIDsSelbeLehrer) . "') ORDER BY schuelerName ASC, schuelerRufname ASC");
				
		while($s = DB::getDB()->fetch_array($daten)) $this->schueler[] = new schueler($s);
		
		
		// Klassen
		
		$daten = DB::getDB()->query("SELECT DISTINCT schuelerKlasse FROM unterricht_besuch NATURAL JOIN schueler WHERE unterrichtID IN ('" . implode("','",$unterrichtIDsSelbeLehrer) . "') ORDER BY length(schuelerKlasse) ASC, schuelerKlasse ASC");
		
		while($d = DB::getDB()->fetch_array($daten)) $this->klassen[] = klasse::getByName($d[0]);
	}
	
	/**
	 * @return fach
	 */
	public function getFach() {
		return fach::getByID($this->data['unterrichtFachID']);
	}
	
	public function getAllKlassenAsList() {
		$klassen = $this->getAllKlassen();
		
		$kls = [];
		for($i = 0; $i < sizeof($klassen); $i++) {
			$kls[] = $klassen[$i]->getKlassenName();
		}
		
		return implode(", ",$kls);
	}
	
	/**
	 * 
	 * @return NULL|lehrer
	 */
	public function getLehrer() {
		return lehrer::getByXMLID($this->data['unterrichtLehrerID']);
	}
	
	public function getBezeichnung() {
		return $this->data['unterrichtBezeichnung'];
	}
	
	public function getArt() {
		return $this->data['unterrichtArt'];
	}
	
	public function getStunden() {
		return $this->data['unterrichtStunden'];
	}
	
	public function isWissenschaftlicht() {
		return $this->data['unterrichtIsWissenschaftlich'] > 0;
	}
	
	public function getStartAsMySQLDate() {
		return $this->data['unterrichtStart'];
	}
	
	public function getEndAsMySQLDate() {
		return $this->data['unterrichtEnde'];
	}
	
	public function isKlassenunterricht() {
		return $this->data['unterrichtIsKlassenunterricht'];
	}
	
	public function getID() {
		return $this->data['unterrichtID'];
	}
	
	public function isPflichtunterricht() {
	    return $this->data['unterrichtArt'] == 'Pflichtunterricht';
	}
	
	public function isWahlunterricht() {
	    return $this->data['unterrichtArt'] == 'Wahlunterricht';
	}

	public function isForderunterricht() {
	    return $this->data['unterrichtArt'] == 'Förderunterricht';
	}
	/**
	 * 
	 * @return klasse[]
	 */
	public function getAllKlassen() {
		return $this->klassen;
	}
	
	/**
	 * 
	 * @return schueler[]
	 */
	public function getSchueler() {

		
		return $this->schueler;
	}
	
	/**
	 * 
	 * @param schueler $schueler
	 */
	public function isSchuelerInUnterricht($schueler) {
	    for($i = 0; $i < sizeof($this->schueler); $i++) {
	        if($this->schueler[$i]->getAsvID() == $schueler->getAsvID()) return true;
	    }
	    
	    return false;
	}
		
	/**
	 * Gekoppelten Unterricht
	 * @return SchuelerUnterricht[]
	 */
	public function getKoppelUnterricht() {
		return $this->koppelUnterricht;
	}
	
	/**
	 * 
	 * @param klasse $klasse
	 */
	public static function getUnterrichtForKlasse($klasse) {
		$daten = DB::getDB()->query("SELECT * FROM unterricht JOIN faecher ON unterrichtFachID=fachID WHERE unterrichtIsKlassenunterricht=1 AND unterrichtID IN (SELECT DISTINCT unterrichtID FROM unterricht_besuch NATURAL JOIN schueler WHERE schuelerKlasse='" . $klasse->getKlassenName() . "') ORDER BY fachKurzForm");
	
		$us = [];
		
		while($u = DB::getDB()->fetch_array($daten)) {
			$us[] = new SchuelerUnterricht($u);
		}
		
		return $us;
		
	
	}
	
	/**
	 * 
	 * @param schueler $schueler
	 */
	public static function getUnterrichtForSchueler($schueler) {
		$daten = DB::getDB()->query("SELECT * FROM unterricht JOIN faecher ON unterrichtFachID=fachID WHERE unterrichtIsKlassenunterricht=1 AND unterrichtID IN (SELECT DISTINCT unterrichtID FROM unterricht_besuch NATURAL JOIN schueler WHERE schuelerAsvID='" . $schueler->getAsvID() . "') ORDER BY fachOrdnung ASC");
		
		$us = [];
		
		while($u = DB::getDB()->fetch_array($daten)) {
			$us[] = new SchuelerUnterricht($u);
		}
		
		return $us;
		
		
	}
	
	/**
	 * 
	 * @param lehrer $lehrer
	 * @return SchuelerUnterricht[]
	 */
	public static function getUnterrichtForLehrer($lehrer, $ignoreKoppel=false) {
		
		$daten = DB::getDB()->query("SELECT * FROM unterricht JOIN faecher ON unterrichtFachID=fachID WHERE unterrichtIsKlassenunterricht=1 AND unterrichtLehrerID='" . $lehrer->getXMLID() . "' ORDER BY fachKurzForm");
		
		$us = [];
		
		$koppelTextShown = [];
		
		
		while($u = DB::getDB()->fetch_array($daten)) {
		    if($ignoreKoppel && $u['unterrichtKoppelText'] != "") {
		        if(!in_array($u['unterrichtKoppelText'], $koppelTextShown)) {
		            $us[] = new SchuelerUnterricht($u);
		            $koppelTextShown[] = $u['unterrichtKoppelText'];
		        }
		    }
		    else {
			 $us[] = new SchuelerUnterricht($u);
		    }
		}
		
		return $us;
		
		
	}
	
	
	/**
	 *
	 * @param lehrer $lehrer
	 * @return SchuelerUnterricht[]
	 */
	public static function getWahlunterricht($lehrer, $ignoreKoppel=false) {
	    
	    $daten = DB::getDB()->query("SELECT * FROM unterricht JOIN faecher ON unterrichtFachID=fachID WHERE unterrichtLehrerID='" . $lehrer->getXMLID() . "' ORDER BY fachKurzForm");
	    
	    $us = [];
	    
	    $koppelTextShown = [];
	    
	    
	    while($u = DB::getDB()->fetch_array($daten)) {
	        if($ignoreKoppel && $u['unterrichtKoppelText'] != "") {
	            if(!in_array($u['unterrichtKoppelText'], $koppelTextShown)) {
	                $us[] = new SchuelerUnterricht($u);
	                $koppelTextShown[] = $u['unterrichtKoppelText'];
	            }
	        }
	        else {
	            $us[] = new SchuelerUnterricht($u);
	        }
	    }
	    
	    return $us;
	    
	    
	}
	
	/**
	 * 
	 * @param lehrer $teacher
	 * @return SchuelerUnterricht[]
	 */
	public static function getSonstigenUnterricht($teacher=null) {
		
		$addSQL = '';
		
		if($teacher != null) {
			$addSQL = " AND unterrichtLehrerID='" . $teacher->getXMLID() . "' ";
		}
		
		$daten = DB::getDB()->query("SELECT * FROM unterricht JOIN faecher ON unterrichtFachID=fachID WHERE unterrichtIsKlassenunterricht=0 $addSQL ORDER BY fachKurzForm");
		
		$us = [];
		
		while($u = DB::getDB()->fetch_array($daten)) {
			$us[] = new SchuelerUnterricht($u);
		}
		
		return $us;
	}
	
	/**
	 *
	 * @param lehrer $teacher
	 * @return SchuelerUnterricht[]
	 */
	public static function getAllWahlUnterricht() {
	    
	    
	    $daten = DB::getDB()->query("SELECT * FROM unterricht JOIN faecher ON unterrichtFachID=fachID WHERE unterrichtArt='Wahlpflichtunterricht' ORDER BY fachKurzForm");
	    
	    $us = [];
	    
	    while($u = DB::getDB()->fetch_array($daten)) {
	        $us[] = new SchuelerUnterricht($u);
	    }
	    
	    return $us;
	}
	
	/**
	 * 
	 * @return SchuelerUnterricht[]
	 */
	public static function getAll() {
	    $daten = DB::getDB()->query("SELECT * FROM unterricht JOIN faecher ON unterrichtFachID=fachID ORDER BY fachKurzForm");
	    
	    $us = [];
	    
	    while($u = DB::getDB()->fetch_array($daten)) {
	        $us[] = new SchuelerUnterricht($u);
	    }
	    
	    return $us;
	}

	
	public static function searchInBezeichnung($search) {
	    $daten = DB::getDB()->query("SELECT * FROM unterricht JOIN faecher ON unterrichtFachID=fachID WHERE unterrichtBezeichnung LIKE '%" . DB::getDB()->escapeString($search) . "%' ORDER BY fachKurzForm");
	    
	    $us = [];
	    
	    while($u = DB::getDB()->fetch_array($daten)) {
	        $us[] = new SchuelerUnterricht($u);
	    }
	    
	    return $us;
	}
	
	
	public static function getByFachAndName($fach, $unterrichtName) {
	    if($fach == null) return null;
	    
	    $daten = DB::getDB()->query_first("SELECT * FROM unterricht WHERE unterrichtFachID='" . $fach->getID() . "' AND unterrichtBezeichnung='" . $unterrichtName . "'");
	    
	    if($daten['unterrichtID'] > 0) return new SchuelerUnterricht($daten);
	    else return null;
	}
	
	/**
	 * 
	 * @param String $unterrichtName
	 * @return SchuelerUnterricht|NULL
	 */
	public static function getByBezeichnung($unterrichtName) {
    
	    $daten = DB::getDB()->query_first("SELECT * FROM unterricht WHERE unterrichtBezeichnung='" . DB::getDB()->escapeString($unterrichtName) . "'");
	    
	    if($daten['unterrichtID'] > 0) return new SchuelerUnterricht($daten);
	    else return null;
	}
	
	/**
	 * 
	 * @param int $id
	 * @return SchuelerUnterricht|NULL
	 */
	public static function getByID($id) {
		$u = DB::getDB()->query_first("SELECT * FROM unterricht WHERE unterrichtID='" . DB::getDB()->escapeString($id) . "'");
		
		if($u['unterrichtID'] > 0) {
			return new SchuelerUnterricht($u);
		}
		
		return null;
	}
}

