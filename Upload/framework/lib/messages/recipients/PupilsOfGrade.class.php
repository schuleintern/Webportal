<?php

class PupilsOfGrade extends MessageRecipient {
	/**
	 * 
	 * @var klasse Klasse
	 */
	private $grade;
	
	/**
	 * 
	 * @param klasse $grade
	 */
	public function __construct($grade, $messageIDs = []) {
		$this->grade = $grade;
		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getSaveString() {
		return 'G:' . $this->grade->getKlassenName();
	}

	public function getDisplayName() {
		return 'Schüler Klasse ' . $this->grade->getKlassenName();
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString,0,2) == 'G:';
	}
	public function getRecipientUserIDs() {
		$schueler = $this->grade->getSchueler(false);
		
		$userIDs = [];
		for($i = 0; $i < sizeof($schueler); $i++) {
			if($schueler[$i]->getSchuelerUserID() > 0) $userIDs[] = $schueler[$i]->getSchuelerUserID();
		}
		
		return $userIDs;
	}
	
	/**
	 * 
	 * @return klasse
	 */
	public function getKlasse() {
		return $this->grade;
	}
	
	public static function getInstanceForSaveString($saveString) {
		
		if(strpos($saveString, "[") > 0) {
			
			$klasse = substr($saveString, 0, strpos($saveString, "["));
			$grade = str_replace("G:","",$klasse);
			
			
			$grade = klasse::getByName($grade);
			
			

			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			if($grade != null) return new PupilsOfGrade($grade, $messageIDs);
			
			else return new UnknownRecipient();
			
		}
		else {
			$grade = substr($saveString,strlen('G:'));
			
			$grade = klasse::getByName($grade);
			
			
			if($grade != null) return new PupilsOfGrade($grade);
			else return new UnknownRecipient();
		}
	}
	
	public function getMissingNames() {
		$schueler = $this->grade->getSchueler(false);
		
		$names = [];
		for($i = 0; $i < sizeof($schueler); $i++) {
			if($schueler[$i]->getSchuelerUserID() == 0) $names[] = $schueler[$i]->getCompleteSchuelerName();
		}
		
		return $names;
	}
	
	public static function getAllInstances() {
		$klassen = klasse::getAllKlassen();
		
		$all = [];
		
		for($i = 0; $i < sizeof($klassen); $i++) {
			$all[] = new PupilsOfGrade($klassen[$i]);
		}
		
		return $all;
	}
	
	public static function getOnly($grades) {
		
		$all = [];
		
		for($g = 0; $g < sizeof($grades); $g++) {
			$klassen = klasse::getByStundenplanName($grades[$g]);
		
	
			$all[] = new PupilsOfGrade($klassen);
		}
		
		return $all;
	}
	
	public static function getInstancesForGrades($grades) {
	    $alle = [];
	    
	    for($i = 0; $i < sizeof($grades); $i++) {
	        $k = self::getOnly($grades[$i]);
	        for($l = 0; $l < sizeof($k); $l++) {
	            $alle = $l[$l];
	        }
	    }
	    
	    return $alle;
	    
	}
}

