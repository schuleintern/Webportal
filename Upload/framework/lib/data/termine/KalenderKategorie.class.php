<?php

class KalenderKategorie {
    
    private static $cache = [];
    
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function getID() {
        return $this->data['kategorieID'];
    }
    
    public function getKalenderID() {
        return $this->data['kategorieKalenderID'];
    }
    
    public function getKategorieName() {
        return $this->data['kategorieName'];
    }
    
    public function getFarbe() {
        return $this->data['kategorieFarbe'];
    }
    
    public function getIcon() {
        return $this->data['kategorieIcon'];
    }
    
    public function setName($name) {
        DB::getDB()->query("UPDATE andere_kalender_kategorie SET kategorieName='" . DB::getDB()->escapeString($name) . "' WHERE kategorieID='" . $this->getID() . "'");
    }
    
    public function setFarbe($farbe) {
        DB::getDB()->query("UPDATE andere_kalender_kategorie SET kategorieFarbe='" . DB::getDB()->escapeString($farbe) . "' WHERE kategorieID='" . $this->getID() . "'");
    }
    
    public function setIcon($icon) {
        DB::getDB()->query("UPDATE andere_kalender_kategorie SET kategorieIcon='" . DB::getDB()->escapeString($icon) . "' WHERE kategorieID='" . $this->getID() . "'");
    }
    
    public function delete() {
        DB::getDB()->query("DELETE FROM andere_kalender_kategorie WHERE kategorieID='" . $this->getID() . "'");
        DB::getDB()->query("UPDATE kalender_andere SET eintragKategorie=0 WHERE eintragKategorie='" . $this->getID() . "'");
    }
    
    public static function createNew($kalenderID, $name, $color) {
        DB::getDB()->query("INSERT INTO andere_kalender_kategorie (kategorieKalenderID, kategorieName, kategorieFarbe) values('" . DB::getDB()->escapeString($kalenderID) . "','" . DB::getDB()->escapeString($name) . "','" . DB::getDB()->escapeString($color) . "')");
    }
    
    public static function getAllForKalender($kalenderID) {
        $dataSQL = DB::getDB()->query("SELECT * FROM andere_kalender_kategorie WHERE kategorieKalenderID='" . intval($kalenderID) . "' ORDER BY kategorieName ASC");
        
        $all = [];
        while($d = DB::getDB()->fetch_array($dataSQL)) $all[] = new KalenderKategorie($d);
        
        return $all;
    }
    
    /**
     * 
     * @param unknown $id
     * @return KalenderKategorie
     */
    public static function getByID($id) {
        if(self::$cache[$id] == null) {
            $dataSQL = DB::getDB()->query_first("SELECT * FROM andere_kalender_kategorie WHERE kategorieID='" . intval($id) . "'");
            if($dataSQL['kategorieID'] > 0) {
                self::$cache[$id] = new KalenderKategorie($dataSQL);
            }
        }
        
        return self::$cache[$id];
        
    }
}

