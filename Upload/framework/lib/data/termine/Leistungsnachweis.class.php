<?php

class Leistungsnachweis {

    private static $terminAk = [
          'SCHULAUFGABE' => 'SA',
          'STEGREIFAUFGABE' => 'EX',
          'KURZARBEIT' => 'KA',
          'PLNW' => 'PLNW',
          'MODUSTEST' => 'MODUS',
          'NACHHOLSCHULAUFGABE' => 'SA (Nachtermin)'
    ];

    private static $terminLangnamen = [
          'SCHULAUFGABE' => 'Schulaufgabe',
          'STEGREIFAUFGABE' => 'Stegreifaufgabe',
          'KURZARBEIT' => 'Kurzarbeit',
          'PLNW' => 'Praktischer Leistungsnachweis',
          'MODUSTEST' => 'Modustest',
          'NACHHOLSCHULAUFGABE' => 'Nachholschulaufgabe'
    ];

    private static $terminFarben = [
        'SCHULAUFGABE' => 'blue',
        'STEGREIFAUFGABE' => 'red',
        'KURZARBEIT' => 'purple',
        'KLASSENTERMIN' => 'green',
        'MODUSTEST' => 'MediumPurple',
        'PLNW' => 'red',
        'NACHHOLSCHULAUFGABE' => 'blue'
    ];

    protected $data;

    protected function __construct($data) {
      $this->data = $data;
    }
    
    public function getDataArray() {
        return $this->data;
    }
    
    /**
     * Liest die ID des Eintrags aus.
     * @return int ID des Eintrags
     */
    public function getID() {
    	return $this->data['eintragID'];
    }

    public function getArt() {
      return $this->data['eintragArt'];
    }

    public function getArtKurztext() {
      return self::$terminAk[$this->data['eintragArt']];
    }

    public function getArtLangtext() {
      return self::$terminLangnamen[$this->data['eintragArt']];
    }

    public function getEintragFarbe() {
      if(DB::getSettings()->getValue('klassenkalender-lnw-color-'.$this->data['eintragArt']) != "") return DB::getSettings()->getValue('klassenkalender-lnw-color-'.$this->data['eintragArt']);
      return self::$terminFarben[$this->data['eintragArt']];
    }

    public function getKlasse() {
      return $this->data['eintragKlasse'];
    }

    public function getDatumStart() {
      return $this->data['eintragDatumStart'];
    }
    
    public function setDatumStart($newDate) {
        DB::getDB()->query("UPDATE kalender_lnw SET eintragDatumStart = '" . DB::getDB()->escapeString($newDate) . "', eintragEintragZeitpunkt=UNIX_TIMESTAMP() WHERE eintragID='" . $this->getID() . "'");    
    }

    public function getBetrifft() {
      return $this->data['eintragBetrifft'];
    }

    public function getLehrer() {
      return $this->data['eintragLehrer'];
    }

    public function getFach() {
      return $this->data['eintragFach'];
    }

    /**
     * @return fach|NULL
     */
    public function getFachObjekt() {
        return Fach::getByKurzform($this->getFach());
    }
    
    public function isAngekuendigt() {
        return $this->getArt() == 'SCHULAUFGABE' || $this->getArt() == 'KURZARBEIT';
    }

    /**
     * Formatierter Eintragzeitpunkt
     * @see functions::makeDateFromTimestamp()
     * @return string
     */
    public function getEintragZeitpunkt() {
      return functions::makeDateFromTimestamp($this->data['eintragEintragZeitpunkt']);
    }

    /**
     * Array der betroffenen Stunden
     * @return int[] Stunden
     */
    public function getStunden() {
      $stunden = [];

      $s = explode(",",trim($this->data['eintragStunden']));
      for($i = 0; $i < sizeof($s); $i++) {
      	if($s[$i] != "" && functions::isNumber($s[$i])) $stunden[] = $s[$i];
      }
      
      return $stunden;
    }

    /**
     *
     * @deprecated Leistungsnachweise haben nicht mehrere Tage
     */
    private function getDatumEnde() {
      return $this->data['eintragDatumStart'];
    }
    
    public function showForNotTeacher() {
        
        $frist = DB::getSettings()->getValue('klassenkalender-lnw-frist-' . $this->getArt());
        
        $show = true;
        
        
        if($frist != "" && $frist > 0) {
            
            $show = false;
            
            $days = DateFunctions::getDifferenceInDays($this->getDatumStart());
            
            // echo($this->getDatumStart()."\r\n");
            // echo($days."\r\n");
            
            
            if($days < $frist) {
                $show = true;
            }
        }
        
        if($show) {
            
            
            if($this->data['eintragAlwaysShow'] > 0) return true;
            
            $isNotEx = $this->getArt() != "STEGREIFAUFGABE" && $this->getArt() != "PLNW";
            
            if($isNotEx) return true;
            
            
            if(DB::getSettings()->getBoolean('klassenkalender-showplnworexafter1day')) {
                $showOn = DateFunctions::addOneDayToMySqlDate($this->getDatumStart());
                
                if(DateFunctions::isSQLDateAtOrAfterAnother(DateFunctions::getTodayAsSQLDate(), $showOn)) return true;
                else return false;
            }
            
            
            
        	return false;
        }
        else return false;
    }
    
    public function isAlwaysShow() {
        return $this->data['eintragAlwaysShow'] > 0;
    }

    /**
     *
     * @param String[] $classes Liste der Klassen Wenn der Array leer ist, dann werden alle Termine ausgelesen
     * @param String $afterDate Datum nach dem die Termine ausgelesen werden sollen (SQL Date)
     */
    public static function getByClass($classes = [], $afterDate = "", $beforeDate = "") {
    	$where = "";
    	
    	$wheres = [];
    	
    	if(sizeof($classes) > 0) {
    	    
    	    for($i = 0; $i < sizeof($classes); $i++) {
    	        $wheres[] = (($i > 0) ? (" OR ") : ("")) . " eintragKlasse = '" . DB::getDB()->escapeString($classes[$i]) . "' ";
    	    }
    	    
    		// $where = " WHERE eintragKlasse IN ('" . implode("','", $classes) . "') ";
    		
    	    $where = " WHERE " . implode("",$wheres);
    	}
    	
    	
    	if($afterDate != "") {
    		if($where != "") $where .= " AND ";
    		else $where .= " WHERE ";

            $where .= " (eintragDatumStart >= '" . $afterDate . "' OR (eintragDatumStart <= '$afterDate' AND eintragDatumEnde >= '$afterDate'))";

    	}
    	
    	if($beforeDate != "") {
    		if($where != "") $where .= " AND ";
    		else $where .= " WHERE ";

            $where .= " (eintragDatumStart <= '" . $beforeDate . "' OR (eintragDatumStart >= '$beforeDate' AND eintragDatumEnde <= '$beforeDate'))";
    	}
    	
    	$all = [];
    	    	 
    	
    	$data = DB::getDB()->query("SELECT * FROM kalender_lnw $where ORDER BY eintragDatumStart ASC, eintragKlasse ASC");
    	while($d = DB::getDB()->fetch_array($data)) {
    		$all[] = new Leistungsnachweis($d);
    	}
    	    	    	
    	return $all;
    }
    
    public static function getBayTeacher($teacher, $afterDate = "", $beforeDate = "") {
    	$where = " WHERE eintragLehrer LIKE '" . $teacher . "'";
    	 
    	if($afterDate != "") {
            $where .= " AND (eintragDatumStart >= '" . $afterDate . "' OR (eintragDatumStart <= '$afterDate' AND eintragDatumEnde >= '$afterDate'))";
        }
    	if($beforeDate != "") {
            $where .= " AND (eintragDatumStart <= '" . $beforeDate . "' OR (eintragDatumStart >= '$beforeDate' AND eintragDatumEnde <= '$beforeDate'))";
        }
    	 
    	$all = [];
    	$data = DB::getDB()->query("SELECT * FROM kalender_lnw $where ORDER BY eintragDatumStart ASC, eintragKlasse ASC");
    	while($d = DB::getDB()->fetch_array($data)) {
    		$all[] = new Leistungsnachweis($d);
    	}
    	 
    	return $all;
    }
    
    /**
     * 
     * @param fach[] $faecher
     * @param string $afterDate
     * @param string $beforeDate
     * @return Leistungsnachweis[]
     */
    public static function getByFaecher($faecher, $afterDate = "", $beforeDate = "") {
        
        $faecherPlain = [];
        
        for($i = 0; $i < sizeof($faecher); $i++) {
            $faecherPlain[] = $faecher[$i]->getKurzform();
        }
        
        $where = " WHERE eintragFach IN ('" . implode("','", $faecherPlain) . "') ";
        
        if($afterDate != "") {
            $where .= " AND eintragDatumStart >= '" . $afterDate . "'";
        }
        if($beforeDate != "") {
            $where .= " AND eintragDatumStart <= '" . $beforeDate . "'";
        }
        
        $all = [];
        $data = DB::getDB()->query("SELECT * FROM kalender_lnw $where ORDER BY eintragDatumStart ASC, eintragKlasse ASC");
        while($d = DB::getDB()->fetch_array($data)) {
            $all[] = new Leistungsnachweis($d);
        }
        
        return $all;
    }
    
    public static function getAll($afterDate = "", $beforeDate = "") {
        $all = [];
        $data = DB::getDB()->query("SELECT * FROM kalender_lnw ORDER BY eintragDatumStart ASC, eintragKlasse ASC");
        while($d = DB::getDB()->fetch_array($data)) {
            $all[] = new Leistungsnachweis($d);
        }
        
        return $all;
    }
    
    public static function getByID($id) {
        $data = DB::getDB()->query_first("SELECT * FROM kalender_lnw WHERE eintragID='" . intval($id) ."'");
        if($data['eintragID'] > 0) return new Leistungsnachweis($data);
        else return null;
    }

}

