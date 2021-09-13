<?php


class Note {
	/**
	 * ID der Note
	 * @var int
	 */
	private $id;
	

	/**
	 * Schüler, dem die Note gehört.
	 * @var schueler
	 */
	private $schueler;
	
	/**
	 * Wert der note (1-6 oder 0-15)
	 * @var int
	 */
	private $wert;
	
	/**
	 * Tendenz der Note
	 * -1 0 1
	 * @var int
	 */
	private $tendenz;
	
	/**
	 * Kommentar zur Note
	 * @var string
	 */
	private $kommentar = '';
	
	/**
	 * SQL Datum der Einzelnote
	 * @var string
	 */
	private $datum = '';
	
	private $arbeitID = 0;
	
	/**
	 * Ist die Note aus einem Nachtermin entstanden?
	 * @var boolean
	 */
	private $isNachtermin = false;
	
	/**
	 * Zählt die Note nur, wenn sie zu einem besseren Ergebnis führt?
	 * @var string
	 */
	private $nurWennBesser = false;
	
	public function __construct($data) {
		if($data['schuelerAsvID'] != '') {
			$this->schueler = new schueler($data);
		}
		
		$this->wert = $data['noteWert'];
		$this->tendenz = $data['noteTendenz'];
		$this->fach = fach::getByID($data['noteFachID']);
		$this->schuljahr = $data['noteSchuljahr'];
		
		$this->kommentar = $data['noteKommentar'];
		$this->datum = $data['noteDatum'];
		$this->arbeitID = $data['noteArbeitID'];
		
		$this->isNachtermin = $data['noteIsNachtermin'];
		
		$this->nurWennBesser = $data['noteNurWennBesser'];
	}
	
	public function getWert() {
		return $this->wert;
	}
	
	public function getTendenz() {
		return $this->tendenz;
	}
	
	public function getKommentar() {
	    return $this->kommentar;
	}
	
	public function getDatum() {
	    return $this->datum;
	}
	
	public function getArbeitID() {
	    return $this->arbeitID;
	}
	
	public function getArbeit() {
	    return NoteArbeit::getbyID($this->arbeitID);
	}
	
	public function isNachtermin() {
	    return $this->isNachtermin;
	}
	
	/**
	 * Note zählt nur, wenn sie zu einem besseren Gesamtergebnis führt.
	 * @return boolean
	 */
	public function nurWennBesser() {
	    return $this->nurWennBesser;
	}
	
	/**
	 * 
	 * @return fach|NULL
	 */
	public function getFach() {
		return $this->fach;
	}
	
	public function getSchuljahr() {
		return $this->schuljahr;
	}
	
	public function getColor() {
        return self::getNotenColor($this->getWert(), $this->getSchueler()->getKlassenObjekt()->getKlassenstufe());
	}
	
	public static function getNotenColor($wert, $jgst=1) {
        if($jgst >= 11) {
            if($wert >= 10) return "#1e8c00";
            if($wert >= 4) return "#f49542";
            return "#820000";
        }
        else {
            if($wert <= 2) return "#1e8c00";
            if($wert <= 4) return "#f49542";
            return "#820000";
        }

	}
	
	public function getDisplayWert() {
	    $note = $this->wert;
	    if($this->tendenz < 0) {
	        $note .= "-";
	    }
	    else if($this->tendenz > 0) {
	        $note .= "+";
	    }
	    
	    if($this->nurWennBesser()) return "(" . $note . ")";
	    
	    return $note;
	}
	
	/**
	 * 
	 * @return schueler
	 */
	public function getSchueler() {
		return $this->schueler;
	}
	
	public function setKommentar($kommentar) {
	    DB::getDB()->query("UPDATE noten_noten SET noteKommentar='" . DB::getDB()->escapeString($kommentar) . "' WHERE noteSchuelerAsvID='" . $this->schueler->getAsvID() . "' AND noteArbeitID='" . $this->getArbeitID() . "'");
	}
	
	public function setDatum($datum) {
	    DB::getDB()->query("UPDATE noten_noten SET noteDatum='" . DB::getDB()->escapeString($datum) . "' WHERE noteSchuelerAsvID='" . $this->schueler->getAsvID() . "' AND noteArbeitID='" . $this->getArbeitID() . "'");
	}
	
	public function setWert($wert) {
	    DB::getDB()->query("UPDATE noten_noten SET noteWert='" . DB::getDB()->escapeString($wert) . "' WHERE noteSchuelerAsvID='" . $this->schueler->getAsvID() . "' AND noteArbeitID='" . $this->getArbeitID() . "'");
	}
	
}

