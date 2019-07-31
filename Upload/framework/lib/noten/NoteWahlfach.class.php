<?php

/**
 * Wahlfach Erfolg
 * @author Christian
 *
 */
class NoteWahlfach {
    private $data;

    /**
     * 
     * @var SchuelerUnterricht
     */
    private $unterricht = null;
    
    private $fach = null;
    
    /**
     * 
     * @var NoteWahlfachNote[]
     */
    private $noten = [];
    
    
    public function __construct($data) {
        $this->data = $data;
        
        $this->fach = fach::getByKurzform($data['fachKurzform']);
        
        $this->unterricht = SchuelerUnterricht::getByFachAndName($this->fach, $data['fachUnterrichtName']);
    }
    
    public function getID() {
        return $this->data['wahlfachID'];
    }
    
    public function getBezeichnung() {
        return $this->data['wahlfachBezeichnung'];
    }
    
    public function getUnterricht() {
        return $this->unterricht;
    }
    
    /**
     * 
     * @return NoteWahlfachNote[]
     */
    public function getNoten() {
        if(sizeof($this->noten) == 0) {
            $sql = DB::getDB()->query("SELECT * FROM noten_wahlfach_noten WHERE wahlfachID='" . $this->getID() . "'");
            
            $this->noten = [];
            
            while($n = DB::getDB()->fetch_array($sql)) {
                $this->noten[] = new NoteWahlfachNote($n);
            }
        }        
        return $this->noten;
    }
    
    /**
     * 
     * @param schueler $schueler
     * @return NoteWahlfachNote
     */
    public function getNoteForSchueler($schueler) {       
        $noten = $this->getNoten();
        
        for($i = 0; $i < sizeof($noten); $i++) {
            if($noten[$i]->getSchuelerAsvID() == $schueler->getAsvID()) return $noten[$i];
        }
        
        return null;
    }
    
    /**
     * 
     * @param lehrer $teacher
     * @param NoteZeugnis $zeugnis
     * @return NoteWahlfach[]
     */
    public static function getAllWahlfachForTeacher($teacher, $zeugnis) {
        
        $unterricht = SchuelerUnterricht::getWahlunterricht($teacher, true);
        
        $all = self::getAll($zeugnis);

        

        
        $forTeacher = [];
        
        for($i = 0; $i < sizeof($all); $i++) {
            for($u = 0; $u < sizeof($unterricht); $u++) {
                if($unterricht[$u]->getID() == $all[$i]->getUnterricht()->getID()) {
                    $forTeacher[] = $all[$i];
                    break;
                }
            }
        }
        
        
        return $forTeacher;
        
    }
    
    
    /**
     * 
     * @param NoteZeugnis $zeugnis
     * @return NoteWahlfach[]
     */
    public static function getAll($zeugnis) {
        $allSQL = DB::getDB()->query("SELECT * FROM noten_wahlfach_faecher WHERE zeugnisID='" . $zeugnis->getID() . "'");
        
        $alle = [];
        
        while($d = DB::getDB()->fetch_array($allSQL)) {
            $alle[] = new NoteWahlfach($d);
        }
        
        return $alle;
    }
    
    public static function getByID($id) {
        $allSQL = DB::getDB()->query_first("SELECT * FROM noten_wahlfach_faecher WHERE wahlfachID='" . $id . "'");
        
        
        if($allSQL['wahlfachID'] > 0) {
            return new NoteWahlfach($allSQL);
        }
        
        return null;
    }
    
    
    /**
     * 
     * @param SchuelerUnterricht $unterricht
     * @param NoteZeugnis $zeugnis
     */
    public static function addUnterrichtAsWahlfachForZeugnis($unterricht, $zeugnis, $bezeichnung) {
        DB::getDB()->query("INSERT INTO noten_wahlfach_faecher
            (
                zeugnisID,
                fachKurzform,
                fachUnterrichtName,
                wahlfachBezeichnung
            )
            values 
            (
                '" . DB::getDB()->escapeString($zeugnis->getID()) . "',
                '" . DB::getDB()->escapeString($unterricht->getFach()->getKurzform()) . "',
                '" . DB::getDB()->escapeString($unterricht->getBezeichnung()) . "',
                '" . DB::getDB()->escapeString($bezeichnung) . "'
            )
        ");
    }
    

    
}