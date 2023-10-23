<?php

class ParentsOfPupilsOfClassRecipient extends MessageRecipient {
	/**
	 * 
	 * @var SchuelerUnterricht Klasse
	 */
    private $unterricht;
	
	/**
	 * 
	 * @param SchuelerUnterricht $unterricht
	 */
	public function __construct($unterricht, $messageIDs = []) {
		$this->unterricht = $unterricht;
		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getSaveString() {
	    return 'POPOC:' . $this->unterricht->getBezeichnung();
	}

	public function getDisplayName() {
        if ($this->unterricht) {
            return 'Eltern der Schüler im Unterricht ' . $this->unterricht->getBezeichnung();
        }
		return '';
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString,0,6) == 'POPOC:';
	}
	public function getRecipientUserIDs() {
		$schueler = $this->unterricht->getSchueler();
		
		$userIDs = [];
		for($i = 0; $i < sizeof($schueler); $i++) {
		    $eltern = $schueler[$i]->getParentsUsers();
		    
		    for($p = 0; $p < sizeof($eltern); $p++) {
		        $userIDs[] = $eltern[$p]->getUserID();
		    }
		    
		}
		
		return $userIDs;
	}
	
	/**
	 * 
	 * @return SchuelerUnterricht
	 */
	public function getUnterricht() {
		return $this->unterricht;
	}
	
	public static function getInstanceForSaveString($saveString) {
		
		if(strpos($saveString, "[") > 0) {
			
			$klasse = substr($saveString, 0, strpos($saveString, "["));
			$grade = str_replace("POPOC:","",$klasse);
			
			
			$unterricht = SchuelerUnterricht::getByBezeichnung($grade);
	
			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			if($grade != null) return new ParentsOfPupilsOfClassRecipient($unterricht, $messageIDs);
			
			else return new UnknownRecipient();
			
		}
		else {
			$grade = substr($saveString,strlen('POPOC:'));
			
			$unterricht = SchuelerUnterricht::getByBezeichnung($grade);			
			
			if($grade != null) return new ParentsOfPupilsOfClassRecipient($unterricht);
			else return new UnknownRecipient();
		}
	}
	
	public function getMissingNames() {
		$schueler = $this->unterricht->getSchueler();
		
		$names = [];
		
		for($i = 0; $i < sizeof($schueler); $i++) {
		    $eltern = $schueler[$i]->getParentsUsers();
		    
		    if(sizeof($eltern) == 0) {
		        $names[] = $schueler[$i]->getCompleteSchuelerName();
		    }
		    
		}
		
		return $names;
	}
	
	public static function getAllInstances() {
		$unterrichte = SchuelerUnterricht::getAll();
		
		$all = [];
		
		for($i = 0; $i < sizeof($unterrichte); $i++) {
			$all[] = new ParentsOfPupilsOfClassRecipient($unterrichte[$i]);
		}
		
		return $all;
	}
	
}

