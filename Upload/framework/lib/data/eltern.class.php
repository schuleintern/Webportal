<?php 

class eltern {
	/**
	 * 
	 * @var schueler[]
	 */
	private $schueler = array();
	
	public function __construct($userID) {
		$schuelers = DB::getDB()->query("SELECT * FROM eltern_email JOIN schueler ON eltern_email.elternSchuelerAsvID=schueler.schuelerAsvID WHERE elternUserID='" . $userID . "'");
		while($s = DB::getDB()->fetch_array($schuelers)) {
			$this->schueler[] = new schueler($s);
		}
	}
	
	public function getKlassenAsArray() {
		$klassen = array();
		
		for($i = 0; $i < sizeof($this->schueler); $i++) {
			$klassen[] = $this->schueler[$i]->getKlasse();
		}
		
		return $klassen;
	}
	
	/**
	 * 
	 * @return klasse[]
	 */
	public function getKlassenObjectsAsArray() {
	    $klassen = array();
	    
	    for($i = 0; $i < sizeof($this->schueler); $i++) {
	        $klassen[] = $this->schueler[$i]->getKlassenObjekt();
	    }
	    
	    return $klassen;
	}
	
	/**
	 * 
	 * @return schueler[]
	 */
	public function getMySchueler() {
		return $this->schueler;
	}
	
	/**
	 * Liest die ASV IDs der Schüler des Elternteils aus.
	 * @return String[]
	 */
	public function getMySchuelerAsvIDs() {
	    $ids = [];
	    for($i = 0; $i < sizeof($this->schueler); $i++) $ids[] = $this->schueler[$i]->getAsvID();
	    return $ids;
	}
}

