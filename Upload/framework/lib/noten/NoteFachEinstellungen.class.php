<?php

class NoteFachEinstellungen {
    
    /**
     * 
     * @var NoteFachEinstellungen[]
     */
    private static $all = [];
    
    /**
     * 
     * @var fach
     */
    private $fach;
    
    private $isVorruckungsfach = false;
    
    private $order;
    
    private $zusammenMitString = '';
    
    /**
     * Fächer, die mit diesem Fach verrechnet werden.
     * @var NoteFachEinstellungen[]
     */
    private $zusammenMit = [];
    
    private $isZusammen = false;
    

    public function getFach() {
        return $this->fach;
    }

    public function getIsVorruckungsfach() {
        return $this->isVorruckungsfach;
    }


    public function getOrder() {
        return $this->order;
    }

    /**
     * 
     * @return NoteFachEinstellungen[]
     */
    public function getZusammenMit() {
        return $this->zusammenMit;
    }
    
    private function initZusammenMit() {
        $zus = explode(",",$this->zusammenMitString);
        for($i = 0; $i < sizeof($zus); $i++) {
            if($zus[$i] != "") {
                $fach = fach::getByKurzform($zus[$i]);
                if($fach != null) {
                    $f = NoteFachEinstellungen::getByFach($fach);
                    if($f != null) {
                        $f->isZusammen = true;
                        $this->zusammenMit[] = $f;
                    }
                }
            }
        }
    }

    public function isZusammen() {
        return $this->isZusammen;
    }

    private function __construct($data) {
        $this->fach = fach::getByKurzform($data['fachKurzform']);
        $this->isVorruckungsfach = $data['fachIsVorrueckungsfach'] > 0;
        $this->order = $data['fachOrder'];
        
        $this->zusammenMitString = $data['fachNoteZusammenMit'];
    }
    
    /**
     * 
     * @param fach $fach
     * @return NoteFachEinstellungen|NULL
     */
    public function getByFach($fach) {
        if(sizeof(self::$all) == 0) {
            $data = DB::getDB()->query("SELECT * FROM noten_fach_einstellungen ORDER BY fachOrder ASC");
            while($d = DB::getDB()->fetch_array($data)) {
                self::$all[] = new NoteFachEinstellungen($d);
            }
            
            for($i = 0; $i < sizeof(self::$all); $i++) {
                self::$all[$i]->initZusammenMit();
            }
        }
        
        for($i = 0; $i < sizeof(self::$all); $i++) {
            if(self::$all[$i]->getFach()->getKurzform() == $fach->getKurzform()) {
                return self::$all[$i];
            }
        }
        
        
        
        return null;
    }
    
    
}