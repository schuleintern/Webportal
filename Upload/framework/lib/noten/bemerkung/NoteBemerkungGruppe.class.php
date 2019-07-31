<?php 

class
NoteBemerkungGruppe {
    private $id;
    private $name;
    private $isMitarbeit = false;
    private $isVerhalten = false;
    
    public function __construct($data) {
        $this->id = $data['gruppeID'];
        $this->name = $data['gruppeName'];
        $this->isMitarbeit = $data['koppelMVNote']  == 'M';
        $this->isVerhalten = $data['koppelMVNote']  == 'V';
    }
    
    public function getID() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function isVerhalten() {
        return $this->isVerhalten;
    }
    
    public function isMitarbeit() {
        return $this->isMitarbeit;
    }
    
    
    /**
     * 
     * @return NoteBemerkungText[]
     */
    public function getTexte() {
        $data = DB::getDB()->query("SELECT * FROM noten_bemerkung_textvorlagen WHERE bemerkungGruppeID='" . $this->getID() . "'");
        
        $alle = [];
        
        while($d = DB::getDB()->fetch_array($data)) {
            $alle[] = new NoteBemerkungText($d);
        }
        
        return $alle;
    }
    
    /**
     * 
     * @return NoteBemerkungGruppe[]
     */
    public static function getAll() {
        $sql = DB::getDB()->query("SELECT * FROM noten_bemerkung_textvorlagen_gruppen");
        
        $alle = [];
        
        while($s = DB::getDB()->fetch_array($sql)) {
            $alle[] = new NoteBemerkungGruppe($s);
        }
        
        return $alle;
    }
    
}


