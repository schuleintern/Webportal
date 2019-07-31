<?php

class NoteZeugnisKlasse {
    
    private static $allCache = [];
    
    private $id = 0;
    /**
     * 
     * @var klasse
     */
    private $klasse = null;
    
    private $datum;
    
    private $notenschluss;
        
    private $schulleitungTeacherObject = null;
    private $klassenleitungTeacherObject = null;
    
    private $schulleitungIsGezeichnet = false;
    private $klassenleitungIsGezeichnet = false; 
    
    private $zeugnis = null;
    
    public function __construct($data) {
        $this->id = $data['zeugnisID'];
        
        $this->klasse = klasse::getByName($data['zeugnisKlasse']);
        
        $this->zeugnis = NoteZeugnis::getByID($data['zeugnisID']);
        
        $this->datum = $data['zeugnisDatum'];
        $this->notenschluss = $data['zeugnisNotenschluss'];
        
        $this->schulleitungTeacherObject = lehrer::getByASVId($data['zeugnisUnterschriftSchulleitungAsvID']);
        $this->klassenleitungTeacherObject = lehrer::getByASVId($data['zeugnisUnterschriftKlassenleitungAsvID']);
        
        $this->klassenleitungIsGezeichnet = $data['zeugnisUnterschriftKlassenleitungAsvIDGezeichnet'] > 0;
        $this->schulleitungIsGezeichnet = $data['zeugnisUnterschriftSchulleitungAsvIDGezeichnet'] > 0;
    }
    
    public function getID() {
        return $this->id;
    }
    
    /**
     * 
     * @return klasse
     */
    public function getKlasse() {
        return $this->klasse;
    }
    
    public function getDatumAsSQLDate() {
        return $this->datum;
    }
    
    public function isNotenschlussVorbei() {
        return DateFunctions::isSQLDateAtOrBeforeAnother($this->getNotenschulussAsSQLDate(), DateFunctions::getTodayAsSQLDate());
    }
    

    /**
     * 
     * @return NULL|NoteZeugnis
     */
    public function getZeugnis() {
        return $this->zeugnis;
    }
    
    /**
     * 
     * @return String
     */
    public function getNotenschulussAsSQLDate() {
        return $this->notenschluss;
    }
    
    /**
     * 
     * @return NULL|lehrer
     */
    public function getSchulleitung() {
        return $this->schulleitungTeacherObject;
    }
    
    /**
     * 
     * @return NULL|lehrer
     */
    public function getKlassenleitung() {
        return $this->klassenleitungTeacherObject;
    }
    
    public function isSchulleitungGezeichnet() {
        return $this->schulleitungIsGezeichnet;
    }
    
    public function isKlassenleitungGezeichnet() {
        return $this->klassenleitungIsGezeichnet;
    }
}