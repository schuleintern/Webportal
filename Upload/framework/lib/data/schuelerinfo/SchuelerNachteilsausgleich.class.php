<?php


class SchuelerNachteilsausgleich {
    private static $cache = [];
    
	private $schueler;
	private $data;

	public function __construct($data) {
		$this->data = $data;
		$this->schueler = new schueler($data);
	}

	public function getArt() {
		switch ($this->data['artStoerung']) {
			case 'rs': return "Rechtschreibstörung";
			case 'lrs': return "Lese- Rechtschreibstörung";
			case 'ls': return "Lesestörung";
			case 'sonst': return 'Sonstige (siehe Kommentar)';
			default: return 'n/a';
		}
	}
	
	public function getArtKurz() {
	    return $this->data['artStoerung'];
	}

	public function hasEnd() {
		return $this->data['gueltigBis'] != '';
	}

	public function getEndDatumAsMySQLDate() {
		return $this->data['gueltigBis'];
	}

	public function getKommentar() {
		return $this->data['kommentar'];
	}

	public function hasNotenschutz() {
		return $this->data['notenschutz'] > 0;
	}

	public function getArbeitszeitverlaengerung() {
		return $this->data['arbeitszeitverlaengerung'];
	}
	
	public function getGewichtung() {
	    return $this->data['gewichtung'];
	}
	
	public function getGewichtungAsText() {
	    if($this->getGewichtung() == '') return '';
	    if($this->getGewichtung() == '12') return '1:2';
	    if($this->getGewichtung() == '21') return '2:1';
	    if($this->getGewichtung() == '11') return '1:1';
	}
	
	public function getInfoString() {
	    // Nachteilsausgleich -  " . $na->getArt() . " - " . $na->getArbeitszeitverlaengerung() . " Zeitzuschlag " . (($na->hasNotenschutz()) ? (" (MIT Notenschutz)") : (" (Ohne Notenschutz)")) . " - " . $na->getKommentar()
	    return $this->getArt() . " - " . $this->getArbeitszeitverlaengerung() . " Zeitzuschlag " . (($this->hasNotenschutz()) ? (" (MIT Notenschutz)") : (" (Ohne Notenschutz)")) . " - Gewichtung: "  . 
	    (($this->getGewichtungAsText() != "") ? $this->getGewichtungAsText() : " normal")
	    . ($this->getKommentar() != "" ? (" - " . $this->getKommentar()) : "");
	}

	public function delete() {
		DB::getDB()->query("DELETE FROM schueler_nachteilsausgleich WHERE schuelerAsvID='" . $this->schueler->getAsvID() . "'");
	}

	/**
	 *
	 * @param schueler $schueler
	 * @return SchuelerNachteilsausgleich|NULL
	 */
	public static function getNachteilsausgleichForSchueler($schueler) {
	    if(self::$cache[$schueler->getAsvID()]['loaded'] > 0) {
	        return self::$cache[$schueler->getAsvID()]['object'];
	    }
		$data = DB::getDB()->query_first("SELECT * FROM schueler_nachteilsausgleich NATURAL JOIN schueler WHERE schuelerAsvID='" . $schueler->getAsvID() . "'");
		if($data['schuelerAsvID'] != '') {
		    self::$cache[$schueler->getAsvID()]['loaded'] = 1;
		    self::$cache[$schueler->getAsvID()]['object'] =  new SchuelerNachteilsausgleich($data);
		    return self::$cache[$schueler->getAsvID()]['object'];
		}
		else {
		    self::$cache[$schueler->getAsvID()]['loaded'] = 1;
		    self::$cache[$schueler->getAsvID()]['object'] =  null;
		    return self::$cache[$schueler->getAsvID()]['object'];
		}
	}

	/**
	 *
	 * @param schueler $schueler
	 * @param String $art
	 * @param String $azv
	 * @param boolean $ns
	 * @param String $kommentar
	 * @param NULL | String $gueltig
	 */
	public static function setNachteilsausgleichForSchueler($schueler, $art, $azv, $ns, $kommentar, $gueltig, $gewichtung) {
		$current = SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($schueler);

		if($current != null) {
			$current->delete();
		}

		DB::getDB()->query("INSERT INTO schueler_nachteilsausgleich (schuelerAsvID, artStoerung, arbeitszeitverlaengerung, notenschutz, kommentar, gueltigBis, gewichtung)

			values
				(
					'" . $schueler->getAsvID() . "',
					'" . DB::getDB()->escapeString($art) . "',
					'" . DB::getDB()->escapeString($azv) . "',
					'" . DB::getDB()->escapeString($ns) . "',
					'" . DB::getDB()->escapeString($kommentar) . "',
					" . ($gueltig == null ? 'NULL' : "'" . $gueltig . "'") . ",
					" . ($gewichtung == null ? 'NULL' : "'" . $gewichtung . "'") . "
				)
		");
	}
}

