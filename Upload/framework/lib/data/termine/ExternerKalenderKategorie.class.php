<?php

class ExternerKalenderKategorie extends AbstractKalenderKategorie {
    
    private static $cache = [];

    public function __construct($data) {
        parent::__construct($data);
    }
    
    public function getID() {
        return md5($this->data['kalenderID'] . "-" . $this->data['kategorieName']);
    }
    
    public function getKalenderID() {
        return $this->data['kalenderID'];
    }
    
    public function getKategorieName() {
        return $this->data['kategorieName'];
    }

    public function getKategorieText() {
        return $this->data['kategorieText'];
    }
    
    public function getFarbe() {
        return $this->data['kategorieFarbe'];
    }
    
    public function getIcon() {
        return $this->data['kategorieIcon'];
    }
    
    public function setText($name) {
        DB::getDB()->query("UPDATE externe_kalender_kategorien SET kategorieText='" . DB::getDB()->escapeString($name) . "' WHERE 
            kalenderID='" . $this->getKalenderID() . "' AND kategorieName='" . $this->getKategorieName() . "'");
    }
    
    public function setFarbe($farbe) {
        DB::getDB()->query("UPDATE externe_kalender_kategorien SET kategorieFarbe='" . DB::getDB()->escapeString($farbe) . "' WHERE 
            kalenderID='" . $this->getKalenderID() . "' AND kategorieName='" . $this->getKategorieName() . "'");
    }
    
    public function setIcon($icon) {
        DB::getDB()->query("UPDATE externe_kalender_kategorien SET kategorieIcon='" . DB::getDB()->escapeString($icon) . "' WHERE 
            kalenderID='" . $this->getKalenderID() . "' AND kategorieName='" . $this->getKategorieName() . "'");
    }

    
    public static function getAllForKalender($kalenderID) {
        $dataSQL = DB::getDB()->query("SELECT * FROM externe_kalender_kategorien WHERE kalenderID='" . intval($kalenderID) . "' ORDER BY kategorieName ASC");
        
        $all = [];
        while($d = DB::getDB()->fetch_array($dataSQL)) $all[] = new ExternerKalenderKategorie($d);
        
        return $all;
    }

    /**
     * @param $kalenderID
     * @param $kategorieName
     * @return mixed
     */
    public static function getByID($kalenderID, $kategorieName) {
        if(self::$cache[$kalenderID . "-" . $kategorieName] == null) {
            $dataSQL = DB::getDB()->query_first("SELECT * FROM externe_kalender_kategorien WHERE 
            kalenderID='" . $kalenderID . "' AND kategorieName='" . $kategorieName . "'");
            if($dataSQL['kalenderID'] > 0) {
                self::$cache[$kalenderID . "-" . $kategorieName] = new ExternerKalenderKategorie($dataSQL);
            }
        }
        
        return self::$cache[$kalenderID . "-" . $kategorieName];
        
    }
}

