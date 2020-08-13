<?php


class NoteArbeit {
    
    private static $cache = [];
    
	private $id;
	private $bereich = "";
	private $name;
	private $lehrerKuerzel = "";
	private $isMuendlich = "";
	private $gewichtung = 1;
	private $fach;
	private $noten = [];
	private $unterrichtID = 0;
	private $datum = null;
	private $unterrichtName = "";
	
	public function __construct($data) {
		$this->id = $data['arbeitID'];
		$this->bereich = $data['arbeitBereich'];
		$this->name = $data['arbeitName'];
		$this->lehrerKuerzel = $data['arbeitLehrerKuerzel'];
		$this->isMuendlich = $data['arbeitIsMuendlich'] > 0;
		$this->gewichtung = $data['arbeitGewicht'];
		$this->fach = fach::getByKurzform($data['arbeitFachKurzform']);
		$this->datum = $data['arbeitDatum'];
		$this->unterrichtName = $data['arbeitUnterrichtName'];
	}
	
	public function hasDatum() {
	    return $this->datum != null;
	}
	
	public function getDatumAsNaturalDate() {
	    return DateFunctions::getNaturalDateFromMySQLDate($this->datum);
	}
	
	public function getDatumAsSQLDate() {
	    return $this->datum;
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getBereich() {
		return $this->bereich;
	}
	public function getName() {
		return $this->name;
	}
	public function getLehrerKuerzel() {
		return $this->lehrerKuerzel;
	}
	public function isMuendlich() {
		return $this->isMuendlich;
	}
	public function getGewichtung() {
		return $this->gewichtung;
	}
	public function getFach() {
		return $this->fach;
	}
	
	public function getUnterrichtName() {
	    return $this->unterrichtName;
	}
	
	public function updateBereich($name) {
	    $this->updateAttr('arbeitBereich', $name);
	}
	
	public function updateName($name) {
	    $this->updateAttr('arbeitName', $name);
	}
	
	public function setDatum($datum) {
	    
	    
	    if($datum != "") {
	        $datum = DateFunctions::getMySQLDateFromNaturalDate($datum);
	    }
	    else $datum = NULL;
	    
	    $this->updateAttr('arbeitDatum', $datum);	    
	}
	
	public function setIsMuendlich($isMuendlich) {
	    $this->updateAttr('arbeitIsMuendlich', $isMuendlich > 0);
	}
	
	public function setGewichtung($gewicht) {
	    $this->updateAttr('arbeitGewicht', $gewicht);
	}
		
	private function updateAttr($name,$value) {
	    if($value != null) 
	       DB::getDB()->query("UPDATE noten_arbeiten SET $name = '" . DB::getDB()->escapeString($value) . "' WHERE arbeitID='" . $this->getID() . "'");
	    else
	       DB::getDB()->query("UPDATE noten_arbeiten SET $name = null WHERE arbeitID='" . $this->getID() . "'");
	       
	}
	
	public function delete() {
		DB::getDB()->query("DELETE FROM noten_arbeiten WHERE arbeitID='" . $this->getID() . "'");
		
		DB::getDB()->query("DELETE FROM noten_noten WHERE noteArbeitID='" . $this->getID() . "'");
	}
	
	/**
	 * 
	 * @param int $fachID
	 * @param String $lehrerKuerzel
	 * @return NoteArbeit[]
	 */
	public static function getByUnterrichtID($unterrichtID, $unterrichtObjekt = null) {
		$alle = [];

		if($unterrichtObjekt != null) $unterricht = $unterrichtObjekt;
		else $unterricht = SchuelerUnterricht::getByID($unterrichtID);
		
		if($unterricht != null) {
		    
		    $faecher = [$unterricht->getFach()->getKurzform()];
		    $bezeichnungen = [$unterricht->getBezeichnung()];
		    
		    $koppel = $unterricht->getKoppelUnterricht();
		    
		    for($i = 0; $i < sizeof($koppel); $i++) {
		        $faecher[] = $koppel[$i]->getFach()->getKurzform();
		        $bezeichnungen[] = $koppel[$i]->getBezeichnung();
		    }
				    		    
		    
    		$sql = DB::getDB()->query("SELECT * FROM noten_arbeiten WHERE arbeitFachKurzform IN('" . implode("','",$faecher) . "') AND arbeitUnterrichtName IN ('" . implode("','", $bezeichnungen) . "')");
    		
    		
    		while($a = DB::getDB()->fetch_array($sql)) {
    			$alle[] = new NoteArbeit($a);
    		}
    		
    		return $alle;
		}
		else {
		    return [];
		}
	}
	
	
	public static function getByKuerzel($kuerzel) {
	    $alle = [];
	    
	        
	    $sql = DB::getDB()->query("SELECT * FROM noten_arbeiten WHERE arbeitLehrerKuerzel LIKE '$kuerzel'");
	        
	        
	    while($a = DB::getDB()->fetch_array($sql)) {
	         $alle[] = new NoteArbeit($a);
	    }
	        
	    return $alle;

	}
	
	/**
	 * 
	 * @param int $id
	 * @return NoteArbeit|NULL
	 */
	public static function getbyID($id) {
	    
	    if(is_object(self::$cache[$id])) return self::$cache[$id];
	    
	    else {
	        $sql = DB::getDB()->query_first("SELECT * FROM noten_arbeiten WHERE arbeitID='" . $id . "'");
	        
	        if($sql['arbeitID'] > 0) {
	            self::$cache[$id] = new NoteArbeit($sql);
	            return self::$cache[$id];
	        }
	        
	        return null;
	    }

	}
	
	
	/**
	 * 
	 * @return Note[]
	 */
	public function getNoten() {
		if(sizeof($this->noten)  == 0) {
			$noten = [];
			
			$sql = DB::getDB()->query("SELECT * FROM noten_noten JOIN schueler ON schuelerAsvID=noteSchuelerAsvID WHERE noteArbeitID='" . $this->id . "'");
			
			while($n = DB::getDB()->fetch_array($sql)) $noten[] = new Note($n);
			
			$this->noten = $noten;
		}
		
		return $this->noten;
	}
	
	public function getSchnitt() {
	    $summe = 0;
	    
	    $noten = $this->getNoten();
	    
	    for($i = 0; $i < sizeof($noten); $i++) $summe += $noten[$i]->getWert();
	    
	    if(sizeof($noten) > 0) return number_format(NotenCalculcator::NoteRunden($summe / sizeof($noten)),2,",",".");
	    else return "---";
	}
	
	/**
	 * 
	 * @param schueler $schueler
	 */
	public function getNoteForSchueler($schueler) {
	    $noten = $this->getNoten();
	    
	    for($i = 0; $i < sizeof($noten); $i++) {
	        if($noten[$i]->getSchueler()->getAsvID() == $schueler->getAsvID()) return $noten[$i];
	    }
	    
	    return null;
	}
}

