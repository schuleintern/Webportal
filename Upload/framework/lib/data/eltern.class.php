<?php 

class eltern {
	/**
	 * 
	 * @var schueler[]
	 */
	private $schueler = array();

    /**
     * @var int
     */
	private $userID = 0;

    /**
     * @var string
     */
	private $email = "";
	
	public function __construct($userID) {
	    $this->userID= $userID;
		$schuelers = DB::getDB()->query("SELECT * FROM eltern_email JOIN schueler ON eltern_email.elternSchuelerAsvID=schueler.schuelerAsvID WHERE elternUserID='" . $userID . "'");
		while($s = DB::getDB()->fetch_array($schuelers)) {
		    $this->email = $s['elternEMail'];
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

    /**
     * @param schueler $schueler
     */
	public function addSchueler($schueler) {
        DB::getDB()->query("INSERT INTO eltern_email (elternEMail, elternSchuelerAsvID, elternUserID) values('" . DB::getDB()->escapeString($this->email) . "','" . $schueler->getAsvID() . "','" . $this->userID . "') ON DUPLICATE KEY UPDATE elternSchuelerAsvID=elternSchuelerAsvID");
    }

    /**
     * @param schueler $schueler
     */
    public function removeSchueler($schueler) {

        if(sizeof($this->schueler) == 1) {
            // Letzter Schüler wird entfernt
            // --> Benutzer löschen (Löscht auch Zuordnungen der Eltern)
            user::getUserByID($this->userID)->deleteUser();
        }
        else {
            DB::getDB()->query("DELETE FROM eltern_email WHERE 
            elternEMail='" . DB::getDB()->escapeString($this->email) . "'
            AND elternSchuelerAsvID='" . $schueler->getAsvID() . "'
            AND elternUserID='" . $this->userID . "'
            ");
        }


    }
}

