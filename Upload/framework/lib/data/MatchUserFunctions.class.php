<?php

class MatchUserFunctions {
	public function __construct() {

	}

	private $userlib;
	public function matchLehrer() {
	    
	    if(DB::getGlobalSettings()->lehrerUserMode == "SYNC") {
	    
    		include_once("userlib.class.php");
    		$this->userlib = new userlib();
    
    		$messages = "";
    		
    		DB::getDB()->query("UPDATE lehrer SET lehrerUserID=0 WHERE lehrerUserID NOT IN (SELECT userID FROM users)");	// Ungültige Zuordnungen löschen
    
    		$lehrer = DB::getDB()->query("SELECT * FROM lehrer WHERE lehrerUserID='0'");
    
    		$alleLehrer = array();
    		while($l = DB::getDB()->fetch_array($lehrer)) {
    			$alleLehrer[] = $l;
    		}
    
    		for($i = 0; $i < sizeof($alleLehrer); $i++) {
    			$l = $alleLehrer[$i];
    			$lehrer = $this->userlib->getLehrerUser($l['lehrerAsvID']);
    			if($lehrer != null) {
    				DB::getDB()->query("UPDATE lehrer SET lehrerUserID='" . $lehrer['userID'] . "' WHERE lehrerAsvID='" . $l['lehrerAsvID'] . "'");
    			}
    			else {
    				$messages .= "Für folgenden Lehrer wurde kein Benutzer gefunden:" . $l['lehrerKuerzel'] . "\r\n";
    			}
    
    		}
    
    		return $messages;
		
	    }
	    else {
	        DB::getDB()->query("UPDATE lehrer SET lehrerUserID=0 WHERE lehrerUserID NOT IN (SELECT userID FROM users)");	// Ungültige Zuordnungen löschen
	        
	        $lehrer = DB::getDB()->query("SELECT * FROM lehrer WHERE lehrerUserID='0'");
	        
	        $alleLehrer = array();
	        while($l = DB::getDB()->fetch_array($lehrer)) {
	            $alleLehrer[] = $l;
	        }
	        
	        for($i = 0; $i < sizeof($alleLehrer); $i++) {
	            $l = $alleLehrer[$i];
	            $lehrer = user::getByASVID($alleLehrer[$i]['lehrerAsvID']);
	            
	            if($lehrer != null) {
	                DB::getDB()->query("UPDATE lehrer SET lehrerUserID='" . $lehrer->getUserID() . "' WHERE lehrerAsvID='" . $l['lehrerAsvID'] . "'");
	            }

	        }
	    }
	}

	public function matchSchueler() {
	    
	    if(DB::getGlobalSettings()->schuelerUserMode == "SYNC") {
    	        
    	    include_once("userlib.class.php");
    	    $this->userlib = new userlib();
    
    	    $messages = "";
    
    	    DB::getDB()->query("UPDATE schueler SET schuelerUserID=0 WHERE schuelerUserID NOT IN (SELECT userID FROM users)");	// Ungültige Zuordnungen löschen
    	    
    		$schueler = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerUserID='0'");
    
    		$alleSchueler = array();
    		while($s = DB::getDB()->fetch_array($schueler)) {
    			$alleSchueler[] = $s;
    		}
    
    		for($i = 0; $i < sizeof($alleSchueler); $i++)  {
    			$schueler = $this->userlib->getPupilUser($alleSchueler[$i]['schuelerAsvID']);
    
    			if($schueler != null) {
    				DB::getDB()->query("UPDATE schueler SET schuelerUserID='" . $schueler['userID'] . "' WHERE schuelerAsvID='" . $alleSchueler[$i]['schuelerAsvID'] . "'");
    			}
    			else {
    				$messages .= "Für folgenden Schüler wurde kein Benutzer gefunden: " . $alleSchueler[$i]['schuelerName'] . ", " . $alleSchueler[$i]['schuelerRufname'] . " (Klasse: " . $alleSchueler[$i]['schuelerKlasse'] . ")";
    				if($alleSchueler[$i]['schuelerAustrittDatum'] != "") {
    					if(DateFunctions::isSQLDateAtOrBeforeAnother($alleSchueler[$i]['schuelerAustrittDatum'], DateFunctions::getTodayAsSQLDate())) {
    						$messages .= " - Schüler bereits ausgetreten";
    					}
    				}
    				$messages .= "\r\n";
    			}
    
    
    		}
    
    		return $messages;
	    }
	    else {
	        DB::getDB()->query("UPDATE schueler SET schuelerUserID=0 WHERE schuelerUserID NOT IN (SELECT userID FROM users)");	// Ungültige Zuordnungen löschen
	        
	        $schueler = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerUserID='0'");
	        
	        $alleSchueler = array();
	        while($s = DB::getDB()->fetch_array($schueler)) {
	            $alleSchueler[] = $s;
	        }
	        
	        for($i = 0; $i < sizeof($alleSchueler); $i++)  {
	            $schueler = user::getByASVID($alleSchueler[$i]['schuelerAsvID']);
	            
	            if($schueler != null) {
	                DB::getDB()->query("UPDATE schueler SET schuelerUserID='" . $schueler->getUserID() . "' WHERE schuelerAsvID='" . $alleSchueler[$i]['schuelerAsvID'] . "'");
	            }	            
	        }
	    }
	}
}


?>