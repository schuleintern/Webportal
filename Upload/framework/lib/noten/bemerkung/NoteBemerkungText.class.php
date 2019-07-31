<?php 

class NoteBemerkungText {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function getID() {
        return $this->data['bemerkungID'];
    }
    
    public function getNote() {
        return $this->data['bemerkungNote'];
    }
    
    /**
     * 
     * @param schueler $schueler
     */
    public function getTextForSchueler($schueler) {
        $text = $this->data['bemerkungTextWeiblich'];
        
        if($schueler->getGeschlecht() == 'm') {
            $text = $this->data['bemerkungTextMaennlich'];
        }
        
        
        return str_replace("#SCHUELER#", $schueler->getRufname(), $text);
    }

    
}


