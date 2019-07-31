<?php

class NotenCalculcator {
    /**
     *
     * @var schueler
     */
    private $schueler;

    /**
     *
     * @var Note[]
     */
    private $noten = [];

    /**
     *
     * @var fach
     */
    private $fach = null;

    /**
     * Wurde eine Notenschutzrechnung durchgeführt?
     * @var string
     */
    private $isNotenschutzRechnung = false;


    /**
     * Notenberechnung schon ausgeführt?
     * @var string
     */
    private $isCalculated = false;

    /**
     * 
     * @var double
     */
    private $schnitt = -1.00;

    /**
     *
     * @var integer
     */
    private $schnittGross = 0;

    /**
     *
     * @var integer
     */
    private $schnittKlein = 0;

    /**
     *
     * @var integer
     */
    private $gewichtGross = 0;

    /**
     *
     * @var integer
     */
    private $gewichtKlein = 0;
    
    
    /**
     * Sind Noten enthalten, die nur zählen, wenn besser?
     * @var boolean
     */
    private $isNotenNurWennBesser = false;

    /**
     *
     * @param schueler $schueler
     * @param Note[] $noten
     */
    public function __construct($schueler, $fach = null, $noten = []) {
        $this->schueler = $schueler;
        $this->fach = $fach;
    }

    /**
     *
     * @param Note $note
     */
    public function addNote($note) {
        $this->noten[] = $note;
        if($note->nurWennBesser()) $this->isNotenNurWennBesser = true;
    }

    /**
     * Wert ist erst sinnvoll, wenn die Note berechnet wurde!
     * @return boolean
     */
    public function isNotenschutzrechnung() {
        return $this->isNotenschutzRechnung;
    }

    public function getSchnitt($ignoreNotenschutz = false) {

        if($ignoreNotenschutz) {
            $this->isCalculated = true;
            $this->schnitt = $this->calcNoten(true); // Beim Ignorieren des NS immer neu ausrechnen
            return $this->schnitt;
        }

        if($this->isCalculated) {
            return $this->schnitt;
        }
        else {
            $this->isCalculated = true;
            $this->schnitt = $this->calcNoten(false);

            return $this->schnitt;
        }
    }

    public function getSchnittGrossMitRechnung($withoutNurWennBesserNoten = false) {
        $noteSchulaufgaben = -1;

        $summeSchulaufgaben = 0;
        $gewichtungSchulaufgaben = 0;
        for($i = 0; $i < sizeof($this->noten); $i++) {
            if($this->noten[$i]->nurWennBesser() && $withoutNurWennBesserNoten) continue;
                if($this->noten[$i]->getArbeit()->getBereich() == 'SA') {

                    $summeSchulaufgaben +=
                    ($this->noten[$i]->getArbeit()->getGewichtung() * $this->noten[$i]->getWert());

                    $gewichtungSchulaufgaben += $this->noten[$i]->getArbeit()->getGewichtung();
                }
        }

        if($summeSchulaufgaben > -1 && $gewichtungSchulaufgaben > 0) {
            $noteSchulaufgaben = $summeSchulaufgaben / $gewichtungSchulaufgaben;
        }

        if($noteSchulaufgaben > 0) {
            
            if($withoutNurWennBesserNoten) {
                return self::NoteRunden($noteSchulaufgaben);
            }
            
            else {
                $result = self::NoteRunden($noteSchulaufgaben);
                
                
                $resultWithOut = $this->getSchnittGrossMitRechnung(true);
                
                if($resultWithOut == "--") return $result;
                
                if($resultWithOut < $result) return $resultWithOut;
                else return $result;
                
            }
            
            
            return self::NoteRunden($noteSchulaufgaben);
        }
        else return '--';
    }

    public function getSchnittKleinMitRechnung($withoutNurWennBesserNoten=false) {
        $noteKlein = -1;

        $summeNoten = 0;
        $gewichtungSchulaufgaben = 0;
        for($i = 0; $i < sizeof($this->noten); $i++) {
            if($this->noten[$i]->nurWennBesser() && $withoutNurWennBesserNoten) continue;
                if($this->noten[$i]->getArbeit()->getBereich() != 'SA') {

                    $summeNoten +=
                    ($this->noten[$i]->getArbeit()->getGewichtung() * $this->noten[$i]->getWert());

                    $gewichte += $this->noten[$i]->getArbeit()->getGewichtung();
                }
        }

        if($summeNoten > -1 && $gewichte > 0) {
            $noteKlein = $summeNoten / $gewichte;
        }

        if($noteKlein > 0) {
            
            if($withoutNurWennBesserNoten) {
                return self::NoteRunden($noteKlein);
            }
            
            else {
                $result = self::NoteRunden($noteKlein);
                
                
                $resultWithOut = $this->getSchnittKleinMitRechnung(true);
                
                if($resultWithOut == "--") return $result;
                
                if($resultWithOut < $result) return $resultWithOut;
                else return $result;
            }      
                 
            
            
            
            return self::NoteRunden($noteKlein);
        }
        else return '--';
    }

    private function calcNoten($ignoreNotenschutz = false, $withOutNurWennBesserNoten = false) {

        if(schulinfo::isGymnasium()) {
            $this->gewichtGross = 2;
            $this->gewichtKlein = 1;

            $gewichtung = NoteGewichtung::getByFachAndJGS($this->fach, $this->schueler->getKlassenObjekt()->getKlassenstufe());

            if($gewichtung != null) {
                $this->gewichtGross = $gewichtung->getGewichtGross();
                $this->gewichtKlein = $gewichtung->getGewichtKlein();
            }

        }

        if($ignoreNotenschutz == false && $this->schueler->getNachteilsausgleich() != null && $this->schueler->getNachteilsausgleich()->hasNotenschutz()) {
            if($this->fach != null) {
                $kf = $this->fach->getKurzform();
                if($kf == 'E' || $kf == 'F' || $kf == 'L'){
                    $this->isNotenschutzRechnung = true;
                    return $this->getSchnittMitNotenschutz();
                }

            }


        }

        if(schulinfo::isGymnasium()) {
            $noteSchulaufgaben = -1;

            $summeSchulaufgaben = 0;
            $gewichtungSchulaufgaben = 0;

            $noteKlein = -1;
            $summeKlein = 0;
            $gewichtungKlein = 0;



            for($i = 0; $i < sizeof($this->noten); $i++) {
                if($this->noten[$i]->nurWennBesser() && $withOutNurWennBesserNoten) continue;
                
                if($this->noten[$i]->getArbeit()->getBereich() == 'SA') {

                    $summeSchulaufgaben +=
                        ($this->noten[$i]->getArbeit()->getGewichtung() * $this->noten[$i]->getWert());

                    $gewichtungSchulaufgaben += $this->noten[$i]->getArbeit()->getGewichtung();
                }
                else {
                    $summeKlein +=
                    ($this->noten[$i]->getArbeit()->getGewichtung() * $this->noten[$i]->getWert());

                    $gewichtungKlein += $this->noten[$i]->getArbeit()->getGewichtung();
                }
            }

            if($summeSchulaufgaben >= 0 && $gewichtungSchulaufgaben > 0) {
                $noteSchulaufgaben = $summeSchulaufgaben / $gewichtungSchulaufgaben;
                $noteSchulaufgaben = ($noteSchulaufgaben);
                $this->schnittGross = $noteSchulaufgaben;
            }


            if($summeKlein >= 0 && $gewichtungKlein > 0) {
                $noteKlein = $summeKlein / $gewichtungKlein;
                $noteKlein = ($noteKlein);

                $this->schnittKlein = $noteKlein;
            }

            
            $result = 0;
            
            if($noteKlein >= 0 && $noteSchulaufgaben >= 0) {
                $result =  self::NoteRunden(
                    (
                        ($this->gewichtGross*$noteSchulaufgaben) + ($this->gewichtKlein*$noteKlein))
                    / 
                        ($this->gewichtGross + $this->gewichtKlein)
                );
            }

            else if($noteKlein >= 0) $result =   self::NoteRunden($noteKlein);
            else if($noteSchulaufgaben >= 0) $result =   self::NoteRunden($noteSchulaufgaben);
            
            
            if($withOutNurWennBesserNoten) return $result;
            
            if($this->isNotenNurWennBesser) {
                $nurWennBesser = $this->calcNoten($ignoreNotenschutz, true);
                if($nurWennBesser <= $result) return $nurWennBesser;
            }
            else return $result;

        }
        else {
            // TODO RECHNUNG für andere Schularten
            return 1;
        }
    }

    public function getSchnittMitNotenschutz() {
        if(schulinfo::isGymnasium()) {
            $na = $this->schueler->getNachteilsausgleich();

            if($na->getGewichtung() == 21) return $this->getSchnitt(true);      // Bei 2:1 normale Berechnung (Berechnung ohne Notenschutz)

            // Runde 1:
            // Alle schriftlichen ausrechnen.

            $noteSchulaufgaben = -1;

            $summeSchulaufgaben = 0;
            $gewichtungSchulaufgaben = 0;

            $noteKlein = -1;
            $summeKlein = 0;
            $gewichtungKlein = 0;

            for($i = 0; $i < sizeof($this->noten); $i++) {
                if(!$this->noten[$i]->getArbeit()->isMuendlich()) {
                    if($this->noten[$i]->getArbeit()->getBereich() == 'SA') {

                        $summeSchulaufgaben +=
                        ($this->noten[$i]->getArbeit()->getGewichtung() * $this->noten[$i]->getWert());

                        $gewichtungSchulaufgaben += $this->noten[$i]->getArbeit()->getGewichtung();
                    }
                    else {
                        $summeKlein +=
                        ($this->noten[$i]->getArbeit()->getGewichtung() * $this->noten[$i]->getWert());

                        $gewichtungKlein += $this->noten[$i]->getArbeit()->getGewichtung();
                    }
                }
            }

            if($summeSchulaufgaben > -1 && $gewichtungSchulaufgaben > 0) {
                $noteSchulaufgaben = $summeSchulaufgaben / $gewichtungSchulaufgaben;
            }


            if($summeKlein >= 0 && $gewichtungKlein > 0) {
                $noteKlein = $summeKlein / $gewichtungKlein;
            }

            $noteSchriftlich = -1;

            if($noteKlein > -1 && $noteSchulaufgaben > -1) {
                $noteSchriftlich = (((2*$noteSchulaufgaben) + $noteKlein) / 3);
            }
            else if($noteKlein > -1) $noteSchriftlich = $noteKlein;
            else if($noteSchulaufgaben > -1) $noteSchriftlich = $noteSchulaufgaben;



            // Runde 2:
            // Alle mündlichen ausrechnen.

            $noteSchulaufgaben = -1;

            $summeSchulaufgaben = 0;
            $gewichtungSchulaufgaben = 0;

            $noteKlein = -1;
            $summeKlein = 0;
            $gewichtungKlein = 0;

            for($i = 0; $i < sizeof($this->noten); $i++) {
                if($this->noten[$i]->getArbeit()->isMuendlich()) {
                    if($this->noten[$i]->getArbeit()->getBereich() == 'SA') {

                        $summeSchulaufgaben +=
                        ($this->noten[$i]->getArbeit()->getGewichtung() * $this->noten[$i]->getWert());

                        $gewichtungSchulaufgaben += $this->noten[$i]->getArbeit()->getGewichtung();
                    }
                    else {
                        $summeKlein +=
                        ($this->noten[$i]->getArbeit()->getGewichtung() * $this->noten[$i]->getWert());

                        $gewichtungKlein += $this->noten[$i]->getArbeit()->getGewichtung();
                    }
                }
            }

            if($summeSchulaufgaben >= 0 && $gewichtungSchulaufgaben > 0) {
                $noteSchulaufgaben = $summeSchulaufgaben / $gewichtungSchulaufgaben;
            }


            if($summeKlein >= 0 && $gewichtungKlein > 0) {
                $noteKlein = $summeKlein / $gewichtungKlein;
            }

            $notemuendlich = -1;

            if($noteKlein > -1 && $noteSchulaufgaben > -1) {
                $notemuendlich = (((2*$noteSchulaufgaben) + $noteKlein) / 3);
            }
            else if($noteKlein >= 0) $notemuendlich = $noteKlein;
            else if($noteSchulaufgaben >= 0) $notemuendlich = $noteSchulaufgaben;


            // Normales Ergebnis:

            $normaleRechnung = $this->getSchnitt(true);

            $neueRechnung = -1;
            if($notemuendlich > -1 && $noteSchriftlich > -1) {
                $neueRechnung = ($noteSchriftlich + $notemuendlich) / 2;
            }
            else if($notemuendlich > -1) {
                $neueRechnung = $notemuendlich;
            }
            else if($noteSchriftlich > -1) {
                $neueRechnung = $noteSchriftlich;
            }

            if($this->fach->getKurzform() == 'E') {
                // Debugger::debugObject($notemuendlich . " - "  . $noteSchriftlich . " - " . $neueRechnung . " - " . $normaleRechnung,1);
            }
            if($neueRechnung < $normaleRechnung) {
                return $neueRechnung;
            }
            else return $normaleRechnung;


        }
        else {
            return 6;
        }
    }

    public function getSchnittGross() {
        return self::NoteRunden($this->schnittGross);
    }

    public function getSchnittKlein() {
        return self::NoteRunden($this->schnittKlein);
    }

    public function getGewichtGross() {
        return $this->gewichtGross;
    }

    public function getGewichtKlein() {
        return $this->gewichtKlein;
    }

    public static function NoteRunden($value) {
        return explode(".",$value)[0] . "." . substr(explode(".",$value)[1],0,2);
    }

}

