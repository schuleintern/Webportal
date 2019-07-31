<?php

class NoteZeugnis {
    
    private static $cache = [];
    
    private $name;
    private $id;
    private $art;
    private $datum;
    private $notenschluss;

    /**
     *
     * @var NoteZeugnisKlasse[] Klassen
     */
    private $zeugnisKlassen = [];

    private function __construct($data) {
        $this->art = $data['zeugnisArt'];
        $this->id = $data['zeugnisID'];
        $this->name = $data['zeugnisName'];
    }

    public function getID() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getArt() {
        return $this->art;
    }

    public function isZwischenzeugnis() {
        return $this->art == 'ZZ';
    }

    public function isJahreszeugnis() {
        return $this->art = 'JZ';
    }

    public function isNotenstandsbericht() {
        return $this->art == 'NB';
    }
    
    public function isAbschlusszeugnis() {
        return $this->art == 'ABZ';
    }
    
    public function isSemesterzeugnis() {
        return $this->art == 'SEMZ';
    }
    
    public function isAbizeugnis() {
        return $this->art == 'ABIZ';
    }
    
    public function getDateAsSQLDate() {
        return $this->datum;
    }
    
    public function getNotenschlussAsSQLDate() {
        return $this->notenschluss;
    }
    
    public function getArtName() {
        switch($this->art) {
            case 'ZZ': return "Zwischenzeugnis";
            case 'JZ': return 'Jahreszeugnis';
            case 'NB': return 'Notenstandsbericht';
            case 'ABZ': return "Abschlusszeugnis";
            case 'SEMZ': return "Semesterzeugnis";
            case 'ABIZ': return "Abizeugnis";
        }
        
        return "Unbekanntes Zeugnis";
    }
    
    public function delete() {
        DB::getDB()->query("DELETE FROM noten_zeugnisse WHERE zeugnisID='" . $this->getID() . "'");
    }


    /**
     * 
     * @return NoteZeugnis[]
     */
    public static function getAll() {
        if(sizeof(self::$cache) == 0) {
            $sql = DB::getDB()->query("SELECT * FROM noten_zeugnisse");
            while($d = DB::getDB()->fetch_array($sql)) self::$cache[] = new NoteZeugnis($d);
        }


        return self::$cache;
        
    }
    
    public static function getByID($id) {
        $all = self::getAll();
        
        for($i = 0; $i < sizeof($all); $i++) {
            if($all[$i]->getID() == $id) return $all[$i];
        }
        
        return null;
    }
    
    /**
     * 
     * @param klasse $klasse
     */
    public function getNoteZeugnisKlasse($klasse) {
        $zeugnisKlassen = $this->getZeugnisKlassen();
        
        for($o = 0; $o < sizeof($zeugnisKlassen); $o++) {
            if($zeugnisKlassen[$o]->getKlasse()->getKlassenName() == $klasse->getKlassenName()) return $zeugnisKlassen[$o];
        }
        
        return false;
    }
    
    /**
     * 
     * @param NoteZeugnisKlasse[] $klasse
     */
    public static function getForKlasse($klasse) {
        $all = self::getAll();
        
        $zeugnisse = [];
        
        for($i = 0; $i < sizeof($all); $i++) {
            $klasseZeugnis = $all[$i]->getNoteZeugnisKlasse($klasse);
            if($klasseZeugnis != null) $zeugnisse[] = $klasseZeugnis;
        }
        
        return $zeugnisse;
    }

    /**
     *
     * @return NoteZeugnisKlasse[]
     */
    public function getZeugnisKlassen() {
        if(sizeof($this->zeugnisKlassen) == 0) {
            $data = DB::getDB()->query("SELECT * FROM noten_zeugnisse_klassen WHERE zeugnisID='" . $this->getID() . "' ORDER BY LENGTH(zeugnisKlasse) ASC, zeugnisKlasse ASC");
            while($d = DB::getDB()->fetch_array($data)) $this->zeugnisKlassen[] = new NoteZeugnisKlasse($d);
        }

        return $this->zeugnisKlassen;
    }
}
