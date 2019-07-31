<?php

class AbsenzVerspaetung {
    private $data = [];
    
    public function __construct($data) {
        $this->data = $data;
    }

    public function getID() {
        return $this->data['verspaetungID'];
    }
    
    public function getSchueler() {
        return schueler::getByAsvID($this->data['verspaetungSchuelerAsvID']);
    }
    
    public function getDateAsSQLDate() {
        return $this->data['verspaetungDate'];
    }
    
    public function getDateAsNaturalDate() {
        return DateFunctions::getNaturalDateFromMySQLDate($this->getDateAsSQLDate());
    }
    
    public function getMinuten() {
        return $this->data['verspaetungMinuten'];
    }
    
    public function getKommentar() {
        return $this->data['verspaetungKommentar'];
    }
    
    public function getStunde() {
        return $this->data['verspaetungStunde'];
    }
    
    public function isBearbeitet() {
        return $this->data['verspaetungBearbeitet'];
    }
    
    public function getBearbeitetKommentar() {
        return $this->data['verspaetungBearbeitetKommentar'];        
    }
    
    public function isBenachrichtigt() {
        return $this->data['verspaetungBenachrichtigt'] > 0;
    }
    
    public function setIsBenachrichtigt() {
        DB::getDB()->query("UPDATE absenzen_verspaetungen SET verspaetungBenachrichtigt=UNIX_TIMESTAMP() WHERE verspaetungID='" . $this->getID() . "'");
    }
    
    public function setIsBearbeitet() {
        DB::getDB()->query("UPDATE absenzen_verspaetungen SET verspaetungBearbeitet=UNIX_TIMESTAMP() WHERE verspaetungID='" . $this->getID() . "'");
    }
    
    public function setIsBearbeitetKommentar($kommentar) {
        DB::getDB()->query("UPDATE absenzen_verspaetungen SET verspaetungBearbeitetKommentar='" . DB::getDB()->escapeString($kommentar) . "' WHERE verspaetungID='" . $this->getID() . "'");
    }
    
    public function setIsNotBearbeitet() {
        DB::getDB()->query("UPDATE absenzen_verspaetungen SET verspaetungBearbeitetKommentar='', verspaetungBearbeitet=0 WHERE verspaetungID='" . $this->getID() . "'");
        
    }
    
    
    /**
     * 
     * @return AbsenzVerspaetung[]
     */
    public static function getAll() {
        $dataSQL = DB::getDB()->query("SELECT * FROM absenzen_verspaetungen");
        
        $allElements = [];
        
        while($d = DB::getDB()->fetch_array($dataSQL)) {
            $allElements[] = new AbsenzVerspaetung($d);
        }
        
        return $allElements;
    }
    
    /**
     * 
     * @param schueler $schueler
     * @return AbsenzVerspaetung[]
     */
    public static function getAllForSchueler($schueler) {
        $dataSQL = DB::getDB()->query("SELECT * FROM absenzen_verspaetungen WHERE verspaetungSchuelerAsvID='" . $schueler->getAsvID() . "'");
        
        $allElements = [];
        
        while($d = DB::getDB()->fetch_array($dataSQL)) {
            $allElements[] = new AbsenzVerspaetung($d);
        }
        
        return $allElements;
    }
    
    /**
     * 
     * @param klasse $klasse
     * @return AbsenzVerspaetung[]
     */
    public static function getAllForKlasse($klasse) {
        $dataSQL = DB::getDB()->query("SELECT * FROM absenzen_verspaetungen WHERE absenzSchuelerAsvID IN (SELECT schuelerAsvID FROM schueler WHERE schuelerKlasse='" . $klasse->getKlassenName() . "')");
        
        $allElements = [];
        
        while($d = DB::getDB()->fetch_array($dataSQL)) {
            $allElements[] = new AbsenzVerspaetung($d);
        }
        
        return $allElements;
    }

}