<?php

/**
 * Note in einem Zeugnis
 * @author Christian Spitschka
 *
 */
class NoteZeugnisNote {
    private $zeugnis = null;
    private $fach = null;
    private $wert = "";
    private $isPaedNote = false;
    private $paedBegruendung = "";
    private $schueler;
    
    public function __construct($data) {
        $this->zeugnis = NoteZeugnis::getByID($data['noteZeugnisID']);
        $this->fach = fach::getByKurzform($data['noteFachKurzform']);
        $this->wert = $data['noteWert'];
        $this->isPaedNote = $data['noteIsPaed'] > 0;
        $this->paedBegruendung = $data['notePaedBegruendung'];
        $this->schueler = schueler::getByAsvID($data['noteSchuelerAsvID']);
    }
    
    public function getZeugnis() {
        return $this->zeugnis;
    }
    
    /**
     * 
     * @return NULL|fach
     */
    public function getFach() {
        return $this->fach;
    }
    
    /**
     * 
     * @return string|unknown
     */
    public function getWert() {
        return $this->wert;
    }
    
    public function getWertText() {
        switch($this->getWert()) {
            case 1: return "sehr gut";
            case 2: return "gut";
            case 3: return "befriedigend";
            case 4: return "ausreichend";
            case 5: return "mangelhaft";
            case 6: return "ungenügend";
        }
        
        return '--------------';
    }
    
    public function isPaedNote() {
        return $this->isPaedNote;
    }
    
    public function getPaedBegruendung() {
        return $this->paedBegruendung;
    }

    /**
     * 
     * @return NULL|schueler
     */
    public function getSchueler() {
        return $this->schueler;
    }
    
    /**
     * 
     * @param NoteZeugnis $zeugnis
     * @param SchuelerUnterricht $unterricht
     */
    public static function getZeugnisNotenForUnterricht($zeugnis, $unterricht) {
        /**
         * 
         * @var schueler[] $schueler
         */
        $schueler = $unterricht->getSchueler();
        
        $asvIDs = [];
        
        for($i = 0; $i < sizeof($schueler); $i++) {
            $asvIDs[] = $schueler[$i]->getAsvID();
        }
        
        if(sizeof($asvIDs) > 0) {
        
            $data = DB::getDB()->query("SELECT * FROM noten_zeugnisse_noten WHERE noteZeugnisID='" . $zeugnis->getID() . "' AND noteFachKurzform='" . $unterricht->getFach()->getKurzform() . "' AND noteSchuelerAsvID IN ('" . implode("','",$asvIDs) . "')");
        
            $noten = [];
            
            while($d = DB::getDB()->fetch_array($data)) {
                $noten[] = new NoteZeugnisNote($d);
            }
            
            return $noten;
            
            
        }
        else return [];
        
    }

    /**
     * @param $zeugnis
     * @param $schueler
     * @return NoteZeugnisNote[]
     */
    public static function getZeugnisNotenForSchueler($zeugnis, $schueler) {

            $data = DB::getDB()->query("SELECT * FROM noten_zeugnisse_noten WHERE noteZeugnisID='" . $zeugnis->getID() . "' AND noteSchuelerAsvID = '" . $schueler->getAsvID() . "'");
            
            $noten = [];
            
            while($d = DB::getDB()->fetch_array($data)) {
                $noten[] = new NoteZeugnisNote($d);
            }
            
            return $noten;


        
    }
    
    /**
     * 
     * @param schueler $schueler
     * @param NoteZeugnis $zeugnis
     * @param fach $fach
     * @param int $wert
     * @param String $kommentar
     * @param boolean $isPaed
     */
    public static function setNoteForSchuelerAndZeugnisAndFach($schueler, $zeugnis, $fach, $wert, $kommentar, $isPaed) {
        DB::getDB()->query("INSERT INTO noten_zeugnisse_noten
            (
                noteSchuelerAsvID,
                noteZeugnisID,
                noteFachKurzform,
                noteWert,
                notePaedBegruendung,
                noteIsPaed)
                values (
                '" . $schueler->getAsvID() . "',
                '" . $zeugnis->getID() . "',
                '" . $fach->getKurzform() . "',
                '" . intval($wert) . "',
                '" . DB::getDB()->escapeString($kommentar) . "',
                '" . ($isPaed > 0 ? 1 : 0) . "')
                ON DUPLICATE KEY UPDATE 
                noteWert='" . intval($wert) . "',
                notePaedBegruendung='" . DB::getDB()->escapeString($kommentar) . "',
                noteIsPaed='" . ($isPaed > 0 ? 1 : 0) . "'");
        
        
    }
    public static function deleteNoteForSchuelerAndFach($schueler, $zeugnis, $fach) {
        DB::getDB()->query("DELETE FROM noten_zeugnisse_noten
                WHERE
                    noteSchuelerAsvID='" . $schueler->getAsvID() . "' AND
                    noteZeugnisID='" . $zeugnis->getID() . "' AND
                    noteFachKurzform='" . $fach->getKurzform() . "'");
        
    }
    
    
}