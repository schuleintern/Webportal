<?php

class UnterrichtsNoten {
    /**
     * 
     * @var SchuelerUnterricht
     */
    private $unterricht;
    
    /**
     * 
     * @var schueler
     */
    private $schueler;
    
    /**
     * 
     * @var NoteArbeit
     */
    private $arbeiten;
    
    /**
     * 
     * @var NoteArbeit[]
     */
    private $schulaufgaben = [];
    
    /**
     *
     * @var NoteArbeit[]
     */
    private $kurzarbeitne = [];
    
    /**
     *
     * @var NoteArbeit[]
     */
    private $exen = [];
    
    /**
     *
     * @var NoteArbeit[]
     */
    private $muendlich = [];
    
    /**
     * 
     * @var float
     */
    private $schnitt = 0;
    
    
    /**
     * Überhaupt Noten vorhanden?
     * @var string
     */
    private $hasNoten = false;
    
    /**
     * 
     * @var NotenCalculcator
     */
    private $notenCalculator = null;
    
    
    
    
    /**
     * 
     * @param SchuelerUnterricht $unterricht
     */
    public function __construct($unterricht, $schueler) {
        $this->unterricht = $unterricht;
        $this->schueler = $schueler;
        $this->arbeiten = NoteArbeit::getByUnterrichtID($this->unterricht->getID(), $unterricht);
        $this->notenCalculator = new NotenCalculcator($this->schueler, $unterricht->getFach());
        
        $gesamtNoten = 0;
        $gesamtGewicht = 0;
        
        for($i = 0; $i < sizeof($this->arbeiten); $i++) {
            
            $noten = $this->arbeiten[$i]->getNoten();
            
            $note = null;
            
            for($n = 0; $n < sizeof($noten); $n++) {
                if($noten[$n]->getSchueler()->getAsvID() == $this->schueler->getAsvID()) {
                    $note = $noten[$n];
                    $this->hasNoten = true;
                    break;
                }
            }
            
            if($note != null) {
                $gesamtNoten += $this->arbeiten[$i]->getGewichtung() * $note->getWert();
                $gesamtGewicht += $this->arbeiten[$i]->getGewichtung();
                
                $this->notenCalculator->addNote($note);
            }
            
            
            
            switch($this->arbeiten[$i]->getBereich()) {
                case 'SA': $this->schulaufgaben[] = $this->arbeiten[$i]; break;
                case 'KA': $this->kurzarbeitne[] = $this->arbeiten[$i]; break;
                case 'EX': $this->exen[] = $this->arbeiten[$i]; break;
                case 'MDL': $this->muendlich[] = $this->arbeiten[$i]; break;
            }
        }
        
        // TODO: Schnitt für Legasthenie richtig berechnen.
        
        if($gesamtGewicht > 0) {
            $this->schnitt = $this->notenCalculator->getSchnitt();
        }
    }
    
    public function getUnterricht() {
        return $this->unterricht;
    }
    
    public function hasNoten() {
        return $this->hasNoten;
    }
    
    /**
     * 
     * @return NotenCalculcator
     */
    public function getNotenCalculator() {
        return $this->notenCalculator;
    }
    
    /**
     * 
     * @return NoteArbeit[]
     */
    public function getSchulaufgaben() {
        return $this->schulaufgaben;
    }
    
    /**
     * Berechnet den Schnitt der großen Leistungsnachweise
     */
    public function getSchnittGross() {
        return $this->notenCalculator->getSchnittGross();
    }
    
    public function getSchnittKlein() {
        return $this->notenCalculator->getSchnittKlein();
    }
    
    public function getGewichtGross() {
        return $this->notenCalculator->getGewichtGross();
    }
    
    public function getGewichtKlein() {
        return $this->notenCalculator->getGewichtKlein();
    }
    
    /**
     * 
     * @return NoteArbeit[]
     */
    public function getKurzarbeiten() {
        return $this->kurzarbeitne;
    }
    
    /**
     * 
     * @return NoteArbeit[]
     */
    public function getExen() {
        return $this->exen;
    }
    
    /**
     * 
     * @return NoteArbeit[]
     */
    public function getMuendlich() {
        return $this->muendlich;
    }
    
    
    /**
     * 
     * @return number
     */
    public function getSchnitt() {
        return $this->schnitt;        
    }
    
    /**
     * 
     * @return boolean
     */
    public function isNotenschutzberechnung() {
        return $this->notenCalculator->isNotenschutzrechnung();
    }
}