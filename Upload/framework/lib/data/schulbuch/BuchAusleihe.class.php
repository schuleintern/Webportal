<?php

class BuchAusleihe {
	private $data;
	
	/**
	 * 
	 * @var Exemplar
	 */
	private $exemplar = null;
	
	/**
	 * 
	 * @var schueler
	 */
	private $schueler = null;
	
	/**
	 * 
	 * @var lehrer
	 */
	private $lehrer = null;
	
	public function __construct($data, $exemplar=null) {
		
		if($exemplar != null)	$this->exemplar =  $exemplar;
		else $this->exemplar = Exemplar::getByID($data['ausleiheExemplarID']);
		
		$this->data = $data;
	}
	
	/**
	 * 
	 * @return int
	 */
	public function getID() {
		return $this->data['ausleiheID'];
	}
	
	/**
	 * 
	 * @return String
	 */
	public function getKommentar() {
	    return $this->data['ausleiheKommentar'];
	}
	
	/**
	 * 
	 * @return Exemplar
	 */
	public function getExemplar() {
		return $this->exemplar;
	}
	
	/**
	 * SQL Date
	 * @return String
	 */
	public function getAusleiheStartDatum() {
		return $this->data['ausleiheStartDatum'];
	}
	
	/**
	 * Enddatum der Ausleihe / "", wenn noch aktiv
	 * @return unknown
	 */
	public function getAusleiheEndDatum() {
		return $this->data['ausleiheEndDatum'];
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isAusleiheActive() {
		return $this->data['ausleiheEndDatum'] == '';
	}
	
	/**
	 * 
	 * @return NULL|schueler
	 */
	public function getSchueler() {
		return schueler::getByAsvID($this->data['ausleiherSchuelerAsvID']);
	}
	
	/**
	 * 
	 * @return NULL|lehrer
	 */
	public function getLehrer() {
		return lehrer::getByAsvID($this->data['ausleiheLehrerAsvID']);
	}
	
	/**
	 * 
	 * @return String
	 */
	public function getAusleiher() {
		return $this->data['ausleiherNameUndKlasse'];
	}
	
	public function delete() {
		DB::getDB()->query("DELETE FROM schulbuch_ausleihe WHERE ausleiheID='" . $this->getID() . "'");
	}
	
	/**
	 * Kommentar festelgen zur Ausleihe.
	 * @param String $text
	 */
	public function setKommentar($text) {
	    DB::getDB()->query("UPDATE schulbuch_ausleihe SET ausleiheKommentar='" . DB::getDB()->escapeString($text) . "' WHERE ausleiheID='" . $this->getID() . "'");
	}
	
	
	/**
	 * 
	 * @param Exemplar $exemplar
	 * @return BuchAusleihe[]
	 */
	public static function getByExemplar($exemplar) {
		$ausleihen = [];
		
		$ausleihenSQL = DB::getDB()->query("SELECT * FROM schulbuch_ausleihe WHERE ausleiheExemplarID='". $exemplar->getID() . "'");#
		
		while($aus = DB::getDB()->fetch_array($ausleihenSQL)) {
			$ausleihen[] = new BuchAusleihe($aus, $exemplar);
		}
		
		return $ausleihen;
	}
	
	/**
	 * 
	 * @param Exemplar $exemplar
	 * @param schueler $schueler
	 * @param string $startDate
	 */
	public static function lendExemplarToSchueler($exemplar, $schueler, $startDate = '') {
		if($startDate == '') $startDate = DateFunctions::getTodayAsSQLDate();
		
		DB::getDB()->query("INSERT INTO schulbuch_ausleihe (
				ausleiheExemplarID,
				ausleiheStartDatum,
				ausleiherNameUndKlasse,
				ausleiherSchuelerAsvID,
				ausleiherUserID
		) values (
			'" . $exemplar->getID() . "',
			'" . $startDate . "',
			'" . DB::getDB()->escapeString($schueler->getCompleteSchuelerName() . ", Klasse " . $schueler->getKlasse()) . "',
			'" . $schueler->getAsvID() . "',
			'" . DB::getUserID() . "'
		)");
	}
	
	/**
	 * 
	 * @param Exemplar $exemplar
	 * @param lehrer $lehrer
	 * @param string $startDate
	 */
	public static function lendExemplarToLehrer($exemplar, $lehrer, $startDate='') {
		if($startDate == '') $startDate = DateFunctions::getTodayAsSQLDate();
		
		DB::getDB()->query("INSERT INTO schulbuch_ausleihe (
				ausleiheExemplarID,
				ausleiheStartDatum,
				ausleiherNameUndKlasse,
				ausleiherLehrerAsvID,
				ausleiherUserID
		) values (
			'" . $exemplar->getID() . "',
			'" . $startDate . "',
			'" . DB::getDB()->escapeString($lehrer->getDisplayNameMitAmtsbezeichnung()) . "',
			'" . $lehrer->getAsvID() . "',
			'" . DB::getUserID() . "'
		)");
	}
	
	public function endAusleihe($endDate='') {
		if($endDate == '') $endDate = DateFunctions::getTodayAsSQLDate();
		
		if($this->isAusleiheActive()) {
			DB::getDB()->query("UPDATE schulbuch_ausleihe SET ausleiheEndDatum='" . $endDate . "', rueckgeberUserID='" . DB::getUserID() . "' WHERE ausleiheID='" . $this->getID() . "'");
		}
	}
	
	/**
	 *
	 * @param schueler[] $schueler
	 * @return BuchAusleihe[]
	 */
	public static function getBySchueler($schueler) {
		$ausleihe = [];
		
		for($i = 0; $i < sizeof($schueler); $i++) {
			$data = DB::getDB()->query("SELECT * FROM schulbuch_ausleihe WHERE ausleiherSchuelerAsvID='" . $schueler[$i]->getAsvID() . "' ORDER BY ausleiheStartDatum DESC");
			while($d = DB::getDB()->fetch_array($data)) {
				$ausleihe[] = new BuchAusleihe($d);
			}
			
		}
		
		return $ausleihe;
	}
	
	
	/**
	 *
	 * @param schueler[] $schueler
	 * @return BuchAusleihe[]
	 */
	public static function getActiveAusleiheBySchueler($schueler) {
	    $ausleihe = [];
	    
	    for($i = 0; $i < sizeof($schueler); $i++) {
	        $data = DB::getDB()->query("SELECT * FROM schulbuch_ausleihe WHERE ausleiherSchuelerAsvID='" . $schueler[$i]->getAsvID() . "' AND ausleiheEndDatum IS NULL ORDER BY ausleiheStartDatum DESC");
	        while($d = DB::getDB()->fetch_array($data)) {
	            $ausleihe[] = new BuchAusleihe($d);
	        }
	        
	    }
	    
	    return $ausleihe;
	}
	
	/**
	 *
	 * @param lehrer[] $lehrer
	 * @return BuchAusleihe[]
	 */
	public static function getByLehrer($lehrer) {
		$ausleihe = [];
		
		for($i = 0; $i < sizeof($lehrer); $i++) {
			$data = DB::getDB()->query("SELECT * FROM schulbuch_ausleihe WHERE ausleiherLehrerAsvID='" . $lehrer[$i]->getAsvID() . "' ORDER BY ausleiheStartDatum DESC");
			while($d = DB::getDB()->fetch_array($data)) {
				$ausleihe[] = new BuchAusleihe($d);
			}
			
		}
		
		return $ausleihe;
	}
}

