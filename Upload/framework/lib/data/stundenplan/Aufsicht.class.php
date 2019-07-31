<?php

class Aufsicht {
    
    /**
     * 
     * @var stundenplandata
     */
    private $stundenplan = null;
    
    private $data;
    
    /**
     * 
     * @param String[] $data
     * @param stundenplandata $stundenplan
     */
    public function __construct($data, $stundenplan) {
        $this->data = $data;
        $this->stundenplan = $stundenplan;
    }
    
    public function getID() {
        return $this->data['aufsichtID'];
    }
    
    /**
     * 
     * @return stundenplandata
     */
    public function getStundenplan() {
        return $this->stundenplan;
    }
    
    public function getLehrerKuerzel() {
        return $this->data['aufsichtLehrerKuerzel'];
    }
    
    public function getVorStunde() {
        return $this->data['aufsichtVorStunde'];
    }
    
    public function getTag() {
        return $this->data['aufsichtTag'];
    }
    
    public function getBereich() {
        return $this->data['aufsichtBereich'];
    }
    
    /**
     * 
     * @param stundenplandata $stundenplanData
     */
    public static function getForStundenplan($stundenplanData) {
        $sql = DB::getDB()->query("SELECT * FROM stundenplan_aufsichten WHERE stundenplanID='" . $stundenplanData->getID() . "'");
        
        $aufsichten = [];
        
        while($a = DB::getDB()->fetch_array($sql)) $aufsichten[] = new Aufsicht($a, $stundenplanData);
        
        return $aufsichten;
    }
    
    public static function importFromUntisFile($file, $stundenplanID) {
        
    }
}