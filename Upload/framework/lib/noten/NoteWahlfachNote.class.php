<?php

/**
 * Wahlfach Erfolg
 * @author Christian
 *
 */
class NoteWahlfachNote {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function getSchuelerAsvID() {
        return $this->data['schuelerAsvID'];
    }
    
    public function getNote() {
        return $this->data['wahlfachNote'];
    }
    
    public function getWahlfach() {
        return NoteWahlfach::getByID($this->data['wahlfachID']);
    }
    
    public function getErfolgText() {
        switch($this->getNote()) {
            case 1: return 'sehr großer Erfolg';
            case 2: return 'großer Erfolg';
            case 3: return 'Erfolg';
            case 4: return 'Teilnahme';
            case 5: return 'kein Erfolg';
        }
        
        return 'n/a';
    }
    
    /**
     * 
     * @param schueler $schueler
     */
    public function getZeugnisText($schueler) {
  
        $text = $schueler->getRufname() . " hat am Wahlfach \"" . $this->getWahlfach()->getBezeichnung() . "\" ";
        
        switch($this->getNote()) {
            case 1: $text .= "mit sehr großem Erfolg "; break;
            case 2: $text .= "mit großem Erfolg "; break;
            case 3: $text .= "Erfolg ";break;
            case 4: $text .= "";break;
            case 5: $text .= "nicht ";break;
        }
        
        $text .= "teilgenommen.";
        
        return $text;
        
    }
    
    /**
     * 
     * @param schueler $schueler
     * @param NoteZeugnis $zeugnis
     */
    public static function getForSchueler($schueler, $zeugnis) {
                
        $sql = DB::getDB()->query("SELECT * FROM noten_wahlfach_noten WHERE schuelerAsvID='" . $schueler->getAsvID() . "' AND wahlfachID IN (SELECT wahlfachID FROM noten_wahlfach_faecher WHERE zeugnisID='" . $zeugnis->getID() . "')");
    
    
        $alle = [];
        
        while($d = DB::getDB()->fetch_array($sql)) {
            $alle[] = new NoteWahlfachNote($d);
        }
        
        return $alle;
    
    }
           
    
    /**
     * 
     * @param schueler $schueler
     * @param int $note
     * @param NoteWahlfach $wahlfach
     */
    public static function setNoteForSchueler($schueler, $note, $wahlfach) {
        DB::getDB()->query("INSERT INTO noten_wahlfach_noten (wahlfachID, schuelerAsvID, wahlfachNote) values('" . $wahlfach->getID() . "','" . $schueler->getAsvID() . "','" . intval($note) . "')

            ON DUPLICATE KEY UPDATE wahlfachNote='" . intval($note) . "'
        ");
    }
    

    
}