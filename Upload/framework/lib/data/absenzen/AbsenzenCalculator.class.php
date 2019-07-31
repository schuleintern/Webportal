<?php

class AbsenzenCalculator {
    /**
     *
     * @var Absenz[]
     */
    private $absenzen = [];

    private $daysWithAbsenzen = [];

    private $calculated = false;

    private $absenzenDayStat = [
        0 => 0,
        1 => 0,
        2 => 0,
        3 => 0,
        4 => 0
    ];

    private $total = 0;
    private $beurlaubt = 0;
    private $entschuldigt = 0;
    private $fpaTotal = 0;


    public function __construct($absenzen) {
        $this->absenzen = $absenzen;
    }

    public function calculate() {

        $absenzen = $this->absenzen;

        for($i = 0; $i < sizeof($absenzen); $i++) {
            $tage = $this->getTotalDaysForAbsenz($absenzen[$i]);
            
            $this->daysWithAbsenzen = [];
            $fpaTage = $this->getTotalFPADaysForAbsenz($absenzen[$i]);
            
            $this->daysWithAbsenzen = [];
            $this->beurlaubt += $this->getBurlaubungTage($absenzen[$i]);

            $this->daysWithAbsenzen = [];
            $this->absenzenDayStat = $this->getAbsenzenDayStat($absenzen[$i], $this->absenzenDayStat);
            
            
            if($absenzen[$i]->isSchriftlichEntschuldigt()) {

                $this->entschuldigt = $this->entschuldigt + $tage + $fpaTage;
            }

            $this->total += $tage;

            $this->fpaTotal += $fpaTage;
        }

    }
    
    public function getTotal() {
        return $this->total;
    }
    
    public function getBeurlaubt() {
        return $this->beurlaubt;
    }
    
    public function getEntschuldigt() {
        return $this->entschuldigt;
    }
    
    public function getFPATotal() {
        return $this->fpaTotal;
    }
    
    public function getDayStat() {
        return $this->absenzenDayStat;
    }
    
    public function getNotenmanagerStat() {
        $absenzen = $this->absenzen;
        
        $stat = array(
            0,0,0,0,0,0,0,0,0,0,0,0,0
        );
        
        $this->daysWithAbsenzen = [];
        
        for($i = 0; $i < sizeof($absenzen); $i++) {
            $stat = $this->addToStat($absenzen[$i], $stat);
        }
        
        return $stat;
    }

    /**
     *
     * @param Absenz $absenz
     * @return number
     */
    private function getTotalDaysForAbsenz($absenz) {
        
        if($absenz->isBeurlaubung() && $absenz->getBeurlaubung()->isInternAbwesend()) return 0;
        
        $klasse = $absenz->getSchueler()->getKlassenObjekt();

        $start = $absenz->getDateAsSQLDate();
        $ende = $absenz->getEnddatumAsSQLDate();

        $anzahl = 0;

        $currentDay = $start;
        while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {

            $tag = DateFunctions::getDayFromMySqlDate($currentDay);

            $count = false;

            if(DB::getSettings()->getBoolean('absenzen-no-duplicate')) {
                if(!in_array($currentDay, $this->daysWithAbsenzen)) {
                    $count = true;
                    $this->daysWithAbsenzen[] = $currentDay;
                }
            } else $count = true;
            
            if(DB::getSettings()->getValue('absenzen-count-absenz-with-minimumhours') > 0) {
                if(sizeof($absenz->getStundenAsArray()) < DB::getSettings()->getValue('absenzen-count-absenz-with-minimumhours')) {
                    $count = false;
                }
            }
            

            if($count && $tag != 0 && $tag != 6 && !Ferien::isFerien($currentDay)) {
                if($klasse->isAnwesend(DateFunctions::getNaturalDateFromMySQLDate($currentDay))) {
                    $anzahl++;
                }
            }

            $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
        }

        return $anzahl;
    }
    
    /**
     * 
     * @param Absenz $absenz
     * @return number
     */
    private function getTotalFPADaysForAbsenz($absenz) {
        
        if($absenz->isBeurlaubung() && $absenz->getBeurlaubung()->isInternAbwesend()) return 0;
        
        $klasse = $absenz->getSchueler()->getKlassenObjekt();
        
        $start = $absenz->getDateAsSQLDate();
        $ende = $absenz->getEnddatumAsSQLDate();
        
        $anzahl = 0;
        
        $currentDay = $start;
        while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {
            
            $tag = DateFunctions::getDayFromMySqlDate($currentDay);
            
            $count = false;
            
            if(DB::getSettings()->getBoolean('absenzen-no-duplicate')) {
                if(!in_array($currentDay, $this->daysWithAbsenzen)) {
                    $count = true;
                    $this->daysWithAbsenzen[] = $currentDay;
                }
            } else $count = true;
            
            if(DB::getSettings()->getValue('absenzen-count-absenz-with-minimumhours') > 0) {
                if(sizeof($absenz->getStundenAsArray()) < DB::getSettings()->getValue('absenzen-count-absenz-with-minimumhours')) {
                    $count = false;
                }
            }
            
            if($count && $tag != 0 && $tag != 6 && !Ferien::isFerien($currentDay)) {
                if(!$klasse->isAnwesend(DateFunctions::getNaturalDateFromMySQLDate($currentDay))) {
                    $anzahl++;
                }
            }
            
            $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
        }
        
        return $anzahl;
    }

    
    /**
     *
     * @param Absenz $absenz
     * @return number
     */
    private function getBurlaubungTage($absenz) {
        
        if($absenz->isBeurlaubung() && $absenz->getBeurlaubung()->isInternAbwesend()) return 0;
       
        $start = $absenz->getDateAsSQLDate();
        $ende = $absenz->getEnddatumAsSQLDate();
        
        $anzahl = 0;
        
        $currentDay = $start;
        while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {
            
            $tag = DateFunctions::getDayFromMySqlDate($currentDay);
            
            $count = false;
            
            if(DB::getSettings()->getBoolean('absenzen-no-duplicate')) {
                if(!in_array($currentDay, $this->daysWithAbsenzen)) {
                    $count = true;
                    $this->daysWithAbsenzen[] = $currentDay;
                }
            } else $count = true;
            
            if($count && $tag != 0 && $tag != 6 && !Ferien::isFerien($currentDay) && $absenz->isBeurlaubung()) {
                $anzahl++;
            }
            
            $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
        }
        
        return $anzahl;
    }

    
    /**
     *
     * @param Absenz $absenz
     * @return number
     */
    private function getAbsenzenDayStat($absenz, $dayStat) {
        
        if($absenz->isBeurlaubung() && $absenz->getBeurlaubung()->isInternAbwesend()) return $dayStat;
        
        $start = $absenz->getDateAsSQLDate();
        $ende = $absenz->getEnddatumAsSQLDate();
        
        $anzahl = 0;
        
        $currentDay = $start;
        
        while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {
            
            $tag = DateFunctions::getWeekDayFromSQLDateISO($currentDay);
                        
            
            if(DB::getSettings()->getBoolean('absenzen-no-duplicate')) {
                if(!in_array($currentDay, $this->daysWithAbsenzen)) {
                    $count = true;
                    $this->daysWithAbsenzen[] = $currentDay;
                }
            } else $count = true;
            
            
            if(DB::getSettings()->getValue('absenzen-count-absenz-with-minimumhours') > 0) {
                if(sizeof($absenz->getStundenAsArray()) < DB::getSettings()->getValue('absenzen-count-absenz-with-minimumhours')) {
                    $count = false;
                }
            }
            
            if($tag == 6 || $tag == 7) {
                $count = false;
            }
            
            $tag = $tag-1;
            
            
            if($count && !Ferien::isFerien($currentDay)) {
                $dayStat[$tag]++;
            }
            
            $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
        }
        
        return $dayStat;
    }
    
    /**
     * 
     */
    private function addToStat($absenz, $stat) {
        if($absenz->isBeurlaubung() && $absenz->getBeurlaubung()->isInternAbwesend()) return $stat;
        
        $start = $absenz->getDateAsSQLDate();
        $ende = $absenz->getEnddatumAsSQLDate();
        
        $anzahl = 0;
        
        $currentDay = $start;
        while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {
            
            $month = DateFunctions::getMonthFromMySqlDate($currentDay);
            
            $tag = DateFunctions::getDayFromMySqlDate($currentDay);
            
            $count = false;
            
            
            
            if(DB::getSettings()->getBoolean('absenzen-no-duplicate')) {
                if(!in_array($currentDay, $this->daysWithAbsenzen)) {
                    $count = true;
                    $this->daysWithAbsenzen[] = $currentDay;
                }
            } else $count = true;
            
            if(DB::getSettings()->getValue('absenzen-count-absenz-with-minimumhours') > 0) {
                if(sizeof($absenz->getStundenAsArray()) < DB::getSettings()->getValue('absenzen-count-absenz-with-minimumhours')) {
                    $count = false;
                }
            }
            
            if($count && $tag != 0 && $tag != 6 && !Ferien::isFerien($currentDay)) {
                $stat[$month]++;
            }
            
            $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
        }
        
        return $stat;
    }

}
