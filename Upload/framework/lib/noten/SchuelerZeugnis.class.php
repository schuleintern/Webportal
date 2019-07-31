<?php 

/**
 * Stellt ein Zeugnis eines Schülers da.
 * @author Christian
 *
 */
class SchuelerZeugnis {
    /**
     * 
     * @var NoteZeugnis
     */
    private $zeugnis = null;
    
    private $schueler = null;
    
    /**
     * @var NoteZeugnisNote[]
     */
    private $zeugnisNoten = [];
    
    
    /**
     * 
     * @param schueler $schueler
     * @param NoteZeugnis $zeugnis
     */
    public function __construct($schueler, $zeugnis) {
        $this->schueler = $schueler;
        $this->zeugnis = $zeugnis;
        
        $unterricht = SchuelerUnterricht::getUnterrichtForSchueler($this->schueler);
        
        
    }
    

}