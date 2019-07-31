<?php

/**
 * Mitarbeit / Verhalten
 * @author Christian
 *
 */
class MVFach {
    /**
     * 
     * @var MV[]
     */
    private $mvs = [];
    
    /**
     * 
     * @var SchuelerUnterricht
     */
    private $unterricht = null;
    
    /**
     * 
     * @var integer
     */
    private $zeugnisID = 0;
    
    public function __construct($mvs, $unterricht, $zeugnisID) {
        $this->mvs = $mvs;
        $this->unterricht = $unterricht;
        $this->zeugnisID = $zeugnisID;
    }
    
    
    /**
     * 
     * @param unknown $schueler
     * @return MV|NULL
     */
    public function getNoteForSchueler($schueler) {
        for($i = 0; $i < sizeof($this->mvs); $i++) {
            if($this->mvs[$i]->getSchueler()->getAsvID() == $schueler->getAsvID()) return $this->mvs[$i];
        }
        
        return null;
    }
    
    /**
     * 
     */
    public function isAllSet() {
        $schueler = $this->unterricht->getSchueler();
        
        if(sizeof($schueler) <= sizeof($this->mvs)) return true;
        else return false;
    }
    
    /**
     * 
     * @param schueler $schueler
     * @param int $mNote
     * @param int $vNote
     */
    public function setNoteForSchueler($schueler, $mNote, $vNote, $kommentar) {
        DB::getDB()->query("INSERT INTO noten_mv (mvFachKurzform, mvUnterrichtName, schuelerAsvID, mNote, vNote, zeugnisID, noteKommentar) values(
            '" . DB::getDB()->escapeString($this->unterricht->getFach()->getKurzform()) . "',
            '" . DB::getDB()->escapeString($this->unterricht->getBezeichnung()) . "',
            '" . DB::getDB()->escapeString($schueler->getAsvID()) . "',
            '" . DB::getDB()->escapeString($mNote) . "',
            '" . DB::getDB()->escapeString($vNote) . "',
            '" . DB::getDB()->escapeString($this->zeugnisID) . "',
            '" . DB::getDB()->escapeString($kommentar) . "'
        )
        ON DUPLICATE KEY UPDATE mNote='" . DB::getDB()->escapeString($mNote) . "',vNote='" . DB::getDB()->escapeString($vNote) . "',noteKommentar='" . DB::getDB()->escapeString($kommentar) . "'
        ");
    }
    
    /**
     * Klassenschnitt für Mitarbeit
     * @return string
     */
    public function getMSchnitt() {
        $summe = 0;
        $anzahl = 0;
        
        for($i = 0; $i < sizeof($this->mvs); $i++) {
            if($this->mvs[$i]->getMNote() > 0) {
                $summe += $this->mvs[$i]->getMNote();
                $anzahl++;
            }
        }
        
        if($anzahl > 0) return number_format(NotenCalculcator::NoteRunden($summe / $anzahl), 2, ".", ",");
        
        return "n/a";
    }
    
    /**
     * Klassenschnitt für Verhalten
     * @return string
     */
    public function getVSchnitt() {
        $summe = 0;
        $anzahl = 0;
        
        for($i = 0; $i < sizeof($this->mvs); $i++) {
            if($this->mvs[$i]->getVNote() > 0) {
                $summe += $this->mvs[$i]->getVNote();
                $anzahl++;
            }
        }
        
        if($anzahl > 0) return number_format(NotenCalculcator::NoteRunden($summe / $anzahl), 2, ".", ",");
        
        return "n/a";
    }
    
    
    /**
     * 
     * @param SchuelerUnterricht $unterricht
     * @param int $zeugnisID
     * @return MVFach|null
     */
    public static function getByUnterrichtID($unterricht, $zeugnisID) {
        $alle = [];
        
        
        if($unterricht != null) {
            
            $faecher = [$unterricht->getFach()->getKurzform()];
            $bezeichnungen = [$unterricht->getBezeichnung()];
            
            $koppel = $unterricht->getKoppelUnterricht();
            
            for($i = 0; $i < sizeof($koppel); $i++) {
                $faecher[] = $koppel[$i]->getFach()->getKurzform();
                $bezeichnungen[] = $koppel[$i]->getBezeichnung();
            }
            
            
            $sql = DB::getDB()->query("SELECT * FROM noten_mv WHERE mvFachKurzform IN('" . implode("','",$faecher) . "') AND mvUnterrichtName IN ('" . implode("','", $bezeichnungen) . "') AND zeugnisID='" . $zeugnisID . "'");;
            
            
            while($a = DB::getDB()->fetch_array($sql)) {
                $alle[] = new MV($a);
            }
            
            return new MVFach($alle, $unterricht, $zeugnisID);
        }
        else {
            return null;
        }
    }
}