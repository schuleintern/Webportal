<?php

class PupilsOfClassRecipient extends MessageRecipient {
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
		return 'POC:' . $this->unterricht->getBezeichnung();
	}

	public function getDisplayName() {
		return 'Schüler Unterricht ' . $this->unterricht->getBezeichnung();
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString,0,4) == 'POC:';
	}
	public function getRecipientUserIDs() {
		$schueler = $this->unterricht->getSchueler();
		
		$userIDs = [];
		for($i = 0; $i < sizeof($schueler); $i++) {
			if($schueler[$i]->getSchuelerUserID() > 0) $userIDs[] = $schueler[$i]->getSchuelerUserID();
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
			$grade = str_replace("POC:","",$klasse);
			
			
			$unterricht = SchuelerUnterricht::getByBezeichnung($grade);
	
			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			if($grade != null) return new PupilsOfClassRecipient($unterricht, $messageIDs);
			
			else return new UnknownRecipient();
			
		}
		else {
			$grade = substr($saveString,strlen('POC:'));
			
			$unterricht = SchuelerUnterricht::getByBezeichnung($grade);			
			
			if($grade != null) return new PupilsOfClassRecipient($unterricht);
			else return new UnknownRecipient();
		}
	}
	
	public function getMissingNames() {
		$schueler = $this->unterricht->getSchueler();
		
		$names = [];
		for($i = 0; $i < sizeof($schueler); $i++) {
			if($schueler[$i]->getSchuelerUserID() == 0) $names[] = $schueler[$i]->getCompleteSchuelerName();
		}
		
		return $names;
	}
	
	public static function getAllInstances() {
		$unterrichte = SchuelerUnterricht::getAll();
		
		$all = [];
		
		for($i = 0; $i < sizeof($unterrichte); $i++) {
		    $all[] = new PupilsOfClassRecipient($unterrichte[$i]);
		}
		
		return $all;
	}
	
}

