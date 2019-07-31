<?php

class SchulleitungRecipient extends MessageRecipient {
	
	public function __construct($messageIDs = []) {		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getSaveString() {
		return 'SL';
	}

	public function getDisplayName() {
		return 'Schulleitung';
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString,0,2) == 'SL';
	}
	public function getRecipientUserIDs() {
	    $schulleitung = schulinfo::getSchulleitungLehrerObjects();
		
		$userIDs = [];
		for($i = 0; $i < sizeof($schulleitung); $i++) {
		    if($schulleitung[$i]->getUserID() > 0) $userIDs[] = $schulleitung[$i]->getUserID();
		}
		
		return $userIDs;
	}
	public static function getInstanceForSaveString($saveString) {
		
		if(strpos($saveString, "[") > 0) {

			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			return new SchulleitungRecipient($messageIDs);
						
		}
		else {
		    if($saveString == 'SL') return new SchulleitungRecipient();
			else return new UnknownRecipient();
		}
	}
	
	public function getMissingNames() {
	    $schulleitung = schulinfo::getSchulleitungLehrerObjects();
	    
	    $names = [];
	    
		for($i = 0; $i < sizeof($schulleitung); $i++) {
		    if($schulleitung[$i]->getUserID() == 0) $names[] = $schulleitung[$i]->getDisplayNameMitAmtsbezeichnung();
		}
		
		return $names;
	}
	
	public static function getAllInstances() {
		return [new SchulleitungRecipient()];
	}
}

