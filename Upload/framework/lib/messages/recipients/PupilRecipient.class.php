<?php

class PupilRecipient extends MessageRecipient {
	
	/**
	 * 
	 * @var schueler lehrer
	 */
	private $schueler;
	
	/**
	 * 
	 * @param schueler $schueler
	 */
	public function __construct($schueler, $messageIDs = []) {
		$this->schueler = $schueler;
		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getDisplayName() {
		return $this->schueler->getCompleteSchuelerName() . " (Klasse " . $this->schueler->getKlasse() . ")";
	}
	
	public function getSaveString() {
		return 'P:' . $this->schueler->getAsvID();
	}
	
	public function getRecipientUserIDs() {
		if($this->schueler->getUserID() > 0) {
			return [$this->schueler->getUserID()];
		}
		else return [];
	}
	
	public function getMissingNames() {
		if($this->schueler->getUserID() == 0) {
			return [$this->getDisplayName()];
		}
		else return [];
	}
		
	/**
	 * 
	 * @return schueler
	 */
	public function getSchueler() {
		return $this->schueler;
	}
	
	/**
	 * 
	 * @return schueler[]
	 */
	public static function getAllInstances() {
		$all = [];
		$schueler = schueler::getAll('LENGTH(schuelerKlasse) ASC, schuelerKlasse, schuelerName ASC, schuelerRufname ASC');
		
		for($i = 0; $i < sizeof($schueler); $i++) $all[] = new PupilRecipient($schueler[$i]);
		
		return $all;
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString, 0,2) == "P:";
	}
	
	public static function getInstanceForSaveString($saveString) {
	    
		if(strpos($saveString, "[") > 0) {
			
			$klasse = substr($saveString, 0, strpos($saveString, "["));
			$asvID = str_replace("P:","",$klasse);
			
			// Debugger::debugObject($asvID,1);
			
			$schueler = schueler::getByASVId($asvID);
			
			
			
			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			if($schueler != null)	return new PupilRecipient($schueler, $messageIDs);
			else return new UnknownRecipient();
			
		}
		else {
			$schueler = substr($saveString,strlen('P:'));
			$schueler = schueler::getByASVId(substr($saveString, 2));
			
			
			if($schueler != null)	return new PupilRecipient($schueler);
			else return new UnknownRecipient();
			
		}
		

	}
	
	public static function getInstancesForGrades($grades) {
		$recipients = [];
		
		for($i = 0; $i < sizeof($grades); $i++) {
			$grade = klasse::getByName($grades[$i]);
			$pupils = $grade->getSchueler(false);
			
			for($p = 0; $p < sizeof($pupils); $p++) {
				$recipients[] = new PupilRecipient($pupils[$p]);
			}
		}
		
		return $recipients;
	}
}