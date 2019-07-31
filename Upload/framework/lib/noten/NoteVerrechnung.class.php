<?php

/**
 * Mitarbeit / Verhalten
 * @author Christian
 *
 */
class NoteVerrechnung {

    private $unterricht1 = null;
    private $unterricht2 = null;

    private $gewicht1 = null;
    private $gewicht2 = null;
    
    private $id = 0;

    public function __construct($data) {
        $fach = fach::getByKurzform($data['verrechnungFach']);

        $this->id = $data['verrechnungID'];
        
        $this->unterricht1 = SchuelerUnterricht::getByFachAndName($fach, $data['verrechnungUnterricht1']);
        $this->unterricht2 = SchuelerUnterricht::getByFachAndName($fach, $data['verrechnungUnterricht2']);
        
        $this->gewicht1 = $data['verrechnungGewicht1'];
        $this->gewicht2 = $data['verrechnungGewicht2'];
    }
    
    public function getID() {
        return $this->id;
    }
    
    /**
     * 
     * @return NULL|SchuelerUnterricht
     */
    public function getUnterricht1() {
        return $this->unterricht1;
    }
    
    /**
     * 
     * @return NULL|SchuelerUnterricht
     */
    public function getUnterricht2() {
        return $this->unterricht2;
    }
    
    public function getGewicht1() {
        return $this->gewicht1;
    }
    
    public function getGewicht2() {
        return $this->gewicht2;
    }
    
    /**
     * 
     * @param SchuelerUnterricht $unterricht
     */
    public function getOtherFach($unterricht) {
        if($unterricht->getID() == $this->unterricht1->getID()) return $this->unterricht2;
        return $this->unterricht1;
    }
    
    /**
     * 
     * @param SchuelerUnterricht $unterricht
     */
    public function getMyGewicht($unterricht) {
        if($unterricht->getID() == $this->unterricht1->getID()) return $this->gewicht1;
        return $this->gewicht2;
    }
    
    /**
     * 
     * @param SchuelerUnterricht $unterricht
     */
    public function getOtherGewicht($unterricht) {
        if($unterricht->getID() == $this->unterricht1->getID()) return $this->gewicht2;
        return $this->gewicht1;
    }
    
    /**
     * 
     * @param SchuelerUnterricht $unterricht
     * @param schueler $schueler
     */
    public static function getVerrechnungForUnterricht($unterricht, $schueler) {
        $fach = $unterricht->getFach()->getKurzform();
        
        $unterrichtName = $unterricht->getBezeichnung();
        
        $vSQL =  DB::getDB()->query("SELECT * FROM noten_verrechnung WHERE verrechnungFach='" . $fach . "' AND 

            (
                verrechnungUnterricht1='" . $unterrichtName . "' OR
                verrechnungUnterricht2 = '" . $unterrichtName . "'
            )

        ");

        $verrechnungen = [];
        
        while($v = DB::getDB()->fetch_array($vSQL)) {
            $verrechnungen[] = new NoteVerrechnung($v);
        }
        
        for($i = 0; $i < sizeof($verrechnungen); $i++) {
            $other = $verrechnungen[$i]->getOtherFach($unterricht);
            
            
            
            if($other != null && $other->isSchuelerInUnterricht($schueler)) return $verrechnungen[$i];
            
        }        
        
        return null;
        
    }
}
