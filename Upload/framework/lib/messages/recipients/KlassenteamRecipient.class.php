<?php


class KlassenteamRecipient extends MessageRecipient {
	
	private $klasse = '';
	
	
	/**
	 * Kein Objekt von Außerhalb
	 */
	public function __construct($klasse, $messageIDs = []) {
		$this->klasse = $klasse;
		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getSaveString() {
		return 'teacher_klasse_' . $this->klasse;
	}
	
	public function getDisplayName() {
		return 'Klassenteam ' . $this->klasse;
	}
	
	public function getRecipientUserIDs() {
		$userIDs = [];
	
		
		$klasse = klasse::getByName($this->klasse);
		
		if($klasse == null) return [];
		
		$lehrer = $klasse->getKlassenlehrer();
		
		for($i = 0; $i < sizeof($lehrer); $i++) {
		    if($lehrer[$i]->getUserID() > 0) {
		        $userIDs[] = $lehrer[$i]->getUserID();
		    }
		}
		
		return $userIDs;
	}
	
	/**
	 * 
	 * @return lehrer[]
	 */
	private function getLehrerRecipients() {
	    $klasse = klasse::getByName($this->klasse);
	    
	    if($klasse == null) return [];
	    
	    $lehrer = $klasse->getKlassenlehrer();
	    
	    return $lehrer;
	}
	
	public static function getAllInstances() {
		$klassen = klasse::getAllKlassen();
		
		$rec = [];
		for($i = 0; $i < sizeof($klassen); $i++) {
		    $rec[] = new KlassenteamRecipient($klassen[$i]->getKlassenName());
		}
		
		return $rec;
		
	}
	
	/**
	 * 
	 * @param String[] $klassen
	 * @return KlassenteamRecipient[]
	 */
	public static function getAllInstancesForGrade($klassen) {
		$klasse = stundenplandata::getCurrentStundenplan()->getAll('grade');
		
		$all = [];
		for($i = 0; $i < sizeof($klasse); $i++) {
			for($o = 0; $o < sizeof($klassen); $o++) {
				if($klassen[$o] == $klasse[$i]) {
					$all[] = new KlassenteamRecipient($klasse[$i]);
					break;
				}
			}	
		}
		
		return $all;
	}

	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		
		return substr($saveString,0,strlen('teacher_klasse_')) == 'teacher_klasse_';
	}

	public function getMissingNames() {
		$alle = $this->getLehrerRecipients();
		
		$missing = [];
		
		for($i = 0; $i < sizeof($alle); $i++) {
			if($alle[$i]->getUserID() > 0) {
			
			}
			else {
				$missing[] = $alle[$i]->getDisplayNameMitAmtsbezeichnung();
			}
		}
		
		
		return $missing;
	}
	
	public static function getInstanceForSaveString($saveString) {
		
		if(strpos($saveString, "[") > 0) {
			
			$klasse = substr($saveString, 0, strpos($saveString, "["));
			$fach = str_replace("teacher_klasse_","",$klasse);
			
			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			return new KlassenteamRecipient($klasse, $messageIDs);
			
		}
		else {
			$klasse = substr($saveString,strlen('teacher_klasse_'));
			
			return new KlassenteamRecipient($klasse);
		}
	}


}

