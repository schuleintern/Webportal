<?php

class PersonalratRecipient extends MessageRecipient {

	public function __construct($messageIDs = []) {		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getSaveString() {
		return 'PR';
	}

	public function getDisplayName() {
		return 'Personalrat';
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString,0,2) == 'PR';
	}
	public function getRecipientUserIDs() {
	    $verwaltung = schulinfo::getPersonalratMitarbeiter();
		
		$userIDs = [];
		for($i = 0; $i < sizeof($verwaltung); $i++) {
		    $userIDs[] = $verwaltung[$i]->getUserID();
		}
		
		return $userIDs;
	}
	public static function getInstanceForSaveString($saveString) {
		
		if(strpos($saveString, "[") > 0) {
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			 return new PersonalratRecipient($messageIDs);
						
		}
		else {
		    if($saveString == 'PR') return new PersonalratRecipient();
			else return new UnknownRecipient();
		}
	}
	
	public function getMissingNames() {
	    return[];
	}
	
	public static function getAllInstances() {
	    return [new PersonalratRecipient()];
	}
}

