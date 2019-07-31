<?php

class ParentRecipient extends MessageRecipient {
	
	/**
	 * 
	 * @var schueler Schüler, dem die Eltern gehören ;-)
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
		
		/*$parents = $this->schueler->getParentsUsers();
		
		$namen = [];
		
		for($i = 0; $i < sizeof($parents); $i++) {
			$namen = $parents[$i]->getDisplayName();
		}
		
		*/
		
		return "Eltern von " . $this->schueler->getCompleteSchuelerName() . " (Klasse " . $this->schueler->getKlasse() . ")";
	}
	
	public function getSaveString() {
		return 'E:' . $this->schueler->getAsvID();
	}
	
	public function getRecipientUserIDs() {
		$parents = $this->schueler->getParentsUsers();
		
		$userIDs = [];
		
		for($i = 0; $i < sizeof($parents); $i++) {
			$userIDs[] = $parents[$i]->getUserID();
		}
		
		return $userIDs;
	}
	
	public function getMissingNames() {
		if(!$this->isAvailible()) {
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
		
		for($i = 0; $i < sizeof($schueler); $i++) $all[] = new ParentRecipient($schueler[$i]);
		
		return $all;
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString, 0,2) == "E:";
	}
	
	public static function getInstanceForSaveString($saveString) {
		if(strpos($saveString, "[") > 0) {
			
		    $schuelerAsvID = substr($saveString, 0, strpos($saveString, "["));

		   
			
		    $schueler = schueler::getByASVId(substr($schuelerAsvID, 2));
			
			
			
			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			if($schueler != null)	return new ParentRecipient($schueler, $messageIDs);
			else return new UnknownRecipient();
			
		}
		else {
			$schueler = substr($saveString,strlen('E:'));
			$schueler = schueler::getByASVId(substr($saveString, 2));
			
			
			if($schueler != null)	return new ParentRecipient($schueler);
			else return new UnknownRecipient();
			
		}
		

	}
	
	public static function getInstancesForGrades($grades) {
		$recipients = [];
		
		for($i = 0; $i < sizeof($grades); $i++) {
			$grade = klasse::getByName($grades[$i]);
			$pupils = $grade->getSchueler(false);
			
			for($p = 0; $p < sizeof($pupils); $p++) {
				$recipients[] = new ParentRecipient($pupils[$p]);
			}
		}
		
		return $recipients;
	}
	
	public function isAvailible() {
		return sizeof($this->getRecipientUserIDs()) > 0;
	}
}