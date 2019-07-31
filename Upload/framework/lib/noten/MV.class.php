<?php

/**
 * Mitarbeit / Verhalten
 * @author Christian
 *
 */
class MV {
    private $data;

    /**
     * 
     * @var SchuelerUnterricht
     */
    private $unterricht = null;
    
    /**
     * 
     * @var schueler
     */
    private $schueler = null;
    
    public function __construct($data) {
        $this->data = $data;
        $this->schueler = schueler::getByAsvID($data['schuelerAsvID']);
    }
    
    public function getMNote() {
        return $this->data['mNote'];
    }
    
    public function getVNote() {
        return $this->data['vNote'];
    }
    
    
    public function getSchueler() {
        return $this->schueler;
    }
    
    public function getKommentar() {
        return $this->data['noteKommentar'];
    }
    
    
    /**
     * 
     * @param SchuelerUnterricht $unterricht
     * @param schueler $schueler
     * @param NoteZeugnis $zeugnis
     */
    public static function getByUnterrichtAndSchueler($unterricht, $schueler, $zeugnis) {
        
        $faecher = [$unterricht->getFach()->getKurzform()];
        $bezeichnungen = [$unterricht->getBezeichnung()];
        
        $koppel = $unterricht->getKoppelUnterricht();
        
        for($i = 0; $i < sizeof($koppel); $i++) {
            $faecher[] = $koppel[$i]->getFach()->getKurzform();
            $bezeichnungen[] = $koppel[$i]->getBezeichnung();
        }
        
        
        $data = DB::getDB()->query_first("SELECT * FROM noten_mv WHERE mvFachKurzform IN('" . implode("','",$faecher) . "') AND mvUnterrichtName IN ('" . implode("','", $bezeichnungen) . "') AND zeugnisID='" . $zeugnis->getID() . "' AND schuelerAsvID='" . $schueler->getAsvID() . "'");;

        
        
       //  $data = DB::getDB()->query_first("SELECT * FROM noten_mv WHERE mvFachKurzform LIKE '" . $unterricht->getFach()->getKurzform() . "' AND mvUnterrichtName='" . $unterricht->getBezeichnung() . "' AND schuelerAsvID='" . $schueler->getAsvID() . "' AND zeugnisID='" . $zeugnis->getID() . "'");
    
        if($data['mvUnterrichtName'] != "") return new MV($data);
        
        return null;
    
    }

    
}