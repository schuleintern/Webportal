<?php


class BibliothekExemplar {
	private $data;
	
	/**
	 * 
	 * @var Schulbuch
	 */
	private $buch = null;
	
	
	public function __construct($data,$schulbuch=null) {
		$this->data = $data;
		if($schulbuch != null) $this->buch = $schulbuch;
		else $this->buch = Schulbuch::getByID($data['exemplarBuchID']);
	}
	
	
	
	/**
	 * 
	 * @return int
	 */
	public function getID() {
		return $this->data['exemplarID'];
	}
	
	/**
	 * 
	 * @return Schulbuch
	 */
	public function getSchulbuch() {
		return $this->buch;
	}
	
	public function delete() {
		DB::getDB()->query("DELETE FROM schulbuch_exemplare WHERE exemplarID='" . $this->getID() . "'");
		DB::getDB()->query("DELETE FROM schulbuch_ausleihe WHERE ausleiheExemplarID='" . $this->getID() . "'");
	}
	
	/**
	 * 
	 * @return String
	 */
	public function getBarcode() {
		return $this->data['exemplarBarcode'];
	}
	
	public function getZustand() {
		if($this->data['exemplarZustand'] == 0) return 'n/a';
		
		switch($this->data['exemplarZustand']) {
			case -1: return 'n/a';
			case 1: return 'Sehr gut';
			case 2: return 'OK';
			case 3: return 'Schlecht';
		}
	}
	
	/**
	 * 
	 * @param String $scan gescanter String (ZUSTAND-1 BIS ZUSTAND-3)
	 * @return boolean
	 */
	public function setZustandScan($scan) {
	    
	    $newZustand = 0;
	    
	    if($scan == 'ZUSTAND-1') {
	        $newZustand = 1;
	        $this->setZustand(1);
	    }
	    if($scan == 'ZUSTAND-2') {
	        $newZustand = 1;
	        $this->setZustand(2);
	    }
	    if($scan == 'ZUSTAND-3') {
	        $newZustand = 1;
	        $this->setZustand(3);
	    }
	    
        if($newZustand > 0) {
            return $newZustand > $this->data['exemplarZustand'];
        }
        else return false;
	}
	
	public function setZustand($zustand) {
	    DB::getDB()->query("UPDATE schulbuch_exemplare SET exemplarZustand='" . DB::getDB()->escapeString($zustand) . "' WHERE exemplarID='" . $this->getID() . "'");
	}
	
	public function getZustandNumber() {
	    if($this->data['exemplarZustand'] == 0) return -1;
	    
	    return $this->data['exemplarZustand'];
	}
	
	public function getAnschaffungsjahr() {
		return $this->data['exemplarAnschaffungsjahr'];
	}
	
	public function isBankbuch() {
		return $this->data['exemplarIsBankbuch'] > 0;
	}
	
	public function getLagerort() {
		return $this->data['exemplarLagerort'];
	}
	
	
	/**
	 * Ist das Exemplar ausgeliehen?
	 * @return boolean
	 */
	public function isAusgeliehen() {
		$ausleihen = BuchAusleihe::getByExemplar($this);
		
		for($i = 0; $i < sizeof($ausleihen); $i++) {
			if($ausleihen[$i]->isAusleiheActive()) return true;			
		}
		
		return false;
	}
	
	/**
	 * 
	 * @return BuchAusleihe[]
	 */
	public function getAusleihen() {
		return BuchAusleihe::getByExemplar($this);
	}
	
	/**
	 * 
	 * @return BuchAusleihe|NULL
	 */
	public function getActiveAusleihe() {
		
		$ausleihen = BuchAusleihe::getByExemplar($this);
		
		for($i = 0; $i < sizeof($ausleihen); $i++) {
			if($ausleihen[$i]->isAusleiheActive()) return $ausleihen[$i];
		}
		
		return null;
	}
	
	/**
	 * 
	 * @param unknown $id
	 * @return Exemplar|NULL
	 */
	public static function getByID($id) {
		$ex = DB::getDB()->query_first("SELECT * FROM schulbuch_exemplare WHERE exemplarID='" . $id . "'");
		
		if($ex['exemplarID'] > 0) return new Exemplar($ex);
		
		return null;
	}
	
	/**
	 *
	 * @param String $barcode
	 * @return Exemplar|NULL
	 */
	public static function getByBarcode($barcode) {
		$ex = DB::getDB()->query_first("SELECT * FROM schulbuch_exemplare WHERE exemplarBarcode='" . $barcode. "'");
		
		if($ex['exemplarID'] > 0) return new Exemplar($ex);
		
		return null;
	}
	
	
	/**
	 * 
	 * @param Schulbuch $schulbuch
	 * @return Exemplar[]
	 */
	public static function getBySchulbuch($schulbuch) {
		$exemplare = [];
		
		$exemplareSQL = DB::getDB()->query("SELECT * FROM schulbuch_exemplare WHERE exemplarBuchID='" . $schulbuch->getID() . "'");
		
		while($e = DB::getDB()->fetch_array($exemplareSQL)) $exemplare[] = new Exemplar($e, $schulbuch);
		
		return $exemplare;
	}
	
	/**
	 *
	 * @param Schulbuch $schulbuch
	 * @param boolean $withBankbuch
	 * @return int
	 */
	public static function getBestandBySchulbuch($schulbuch,$withBankbuch) {
		
		$anzahl = DB::getDB()->query_first("SELECT count(*) FROM schulbuch_exemplare WHERE exemplarBuchID='" . $schulbuch->getID() . "'" . ($withBankbuch ? (" AND exemplarIsBankbuch=1") : (" AND exemplarIsBankbuch=0")));
		
		
		return $anzahl[0];
	}
	
	public static function getLentBestand($schulbuch,$withBankbuch) {
		
		$anzahl = DB::getDB()->query_first("SELECT count(*) FROM schulbuch_exemplare WHERE exemplarBuchID='" . $schulbuch->getID() . "' AND exemplarID IN (SELECT ausleiheExemplarID FROM schulbuch_ausleihe WHERE 	ausleiheEndDatum IS NULL)" . ($withBankbuch ? (" AND exemplarIsBankbuch=1") : (" AND exemplarIsBankbuch=0")));
		
		
		return $anzahl[0];
	}
	
	/**
	 * Leiht das Buch an den Schüler aus
	 * @param schueler $schueler
	 */
	public function lendToSchueler($schueler, $startDate = '') {
		BuchAusleihe::lendExemplarToSchueler($this, $schueler, $startDate);
	}
	

	/**
	 * 
	 * @param lehrer $lehrer
	 * @param string $startDate
	 */
	public function lendToLehrer($lehrer, $startDate ='' ) {
		BuchAusleihe::lendExemplarToLehrer($this, $lehrer);
	}
	
	/**
	 * 
	 * @param Schulbuch $schulbuch
	 * @param String $barcode
	 * @param int $zustand
	 * @param int $anschaffungsjahr
	 */
	public static function addExemplar($schulbuch, $barcode, $zustand, $anschaffungsjahr, $isBankbuch, $lagerort) {
		DB::getDB()->query("INSERT INTO schulbuch_exemplare 
			(
				exemplarBuchID,
				exemplarBarcode,
				exemplarZustand,
				exemplarAnschaffungsjahr,
				exemplarIsBankbuch,
				exemplarLagerort,
				exemplarErfasserUserID
			)
			values(
				'" . $schulbuch->getID() . "',
				'" . DB::getDB()->escapeString($barcode) . "',
				'" . $zustand . "',
				'" . $anschaffungsjahr . "',
				'" . $isBankbuch . "',
				'" . DB::getDB()->escapeString($lagerort) . "',
				'" . DB::getUserID() . "'
			)
		");
	}
	

}

