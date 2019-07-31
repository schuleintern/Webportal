<?php


/**
 * Notengewichtung
 * @author Christian Christian
 * @license GPLv3
 */

class NoteGewichtung {
    /**
     * 
     * @var NoteGewichtung[]
     */
    private static $cache = [];
    
    private $fach = null;
    
    private $jgs = 0;
    
    private $gewichtKlein = 1;
    
    private $gewichtGross = 1;
    
    public function __construct($data) {
        $this->fach = fach::getByKurzform($data['fachKuerzel']);
        $this->jgs = $data['fachJahrgangsstufe'];
        $this->gewichtKlein = $data['fachGewichtKlein'];
        $this->gewichtGross = $data['fachGewichtGross'];
    }
    
    /**
     * 
     * @return fach|NULL
     */
    public function getFach() {
        return $this->fach;
    }
    
    public function getJGS() {
        return $this->jgs;
    }
    
    /**
     * 
     * @return int
     */
    public function getGewichtKlein() {
       return $this->gewichtKlein; 
    }

    /**
     * 
     * @return int
     */
    public function getGewichtGross() {
        return $this->gewichtGross;
    }
    
    public function delete() {
        DB::getDB()->query("DELETE FROM noten_gewichtung WHERE fachKuerzel='" . $this->getFach()->getKurzform() . "' AND fachJahrgangsstufe='" . $this->getJGS() . "'");
    }
    
    private static function initCache() {
        if(sizeof(self::$cache) == 0) {
            $dataSQL = DB::getDB()->query("SELECT * FROM noten_gewichtung ORDER BY fachJahrgangsstufe");
            while($n = DB::getDB()->fetch_array($dataSQL)) {
                self::$cache[] = new NoteGewichtung($n);
            }
        }
    }
    
    /**
     * 
     * @param fach $fach
     * @param int $jgs
     * @return NoteGewichtung|null
     */
    public static function getByFachAndJGS(fach $fach, $jgs) {
        self::initCache();
        
        if($fach == null) return null;
        
        for($i = 0; $i < sizeof(self::$cache); $i++) {
           if(self::$cache[$i]->getFach()->getID() == $fach->getID() && self::$cache[$i]->getJGS() == $jgs) return self::$cache[$i];
        }
        
        return null;
    }
    
    public static function getAll() {
        self::initCache();
        
        return self::$cache;
    }
    
    /**
     * 
     * @param fach $fach
     * @param unknown $jgs
     * @param unknown $gewichtG
     * @param unknown $gewichtK
     */
    public static function addGewichtung(fach $fach, $jgs, $gewichtG, $gewichtK) {
        DB::getDB()->query("INSERT INTO noten_gewichtung 
            (
                fachKuerzel,
                fachJahrgangsstufe,
                fachGewichtKlein,
                fachGewichtGross)
            values(
                '" . $fach->getKurzform() . "',
                '" . intval($jgs) . "',
                '" . intval($gewichtK) . "',
                '" . intval($gewichtG) . "'
            )
            ON DUPLICATE KEY UPDATE fachGewichtKlein='" . intval($gewichtK) . "',
                fachGewichtGross='" . intval($gewichtG) . "'");
    }
}

