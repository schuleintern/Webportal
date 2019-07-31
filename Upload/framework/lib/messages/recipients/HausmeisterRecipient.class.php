<?php

class HausmeisterRecipient extends MessageRecipient {

	public function __construct($messageIDs = []) {		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getSaveString() {
		return 'HM';
	}

	public function getDisplayName() {
		return 'Hausmeister';
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString,0,2) == 'HM';
	}
	public function getRecipientUserIDs() {
	    $verwaltung = schulinfo::getHausmeister();
		
		$userIDs = [];
		for($i = 0; $i < sizeof($verwaltung); $i++) {
		    $userIDs[] = $verwaltung[$i]->getUserID();
		}
		
		return $userIDs;
	}
	public static function getInstanceForSaveString($saveString) {
		
		if(strpos($saveString, "[") > 0) {
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			return new HausmeisterRecipient($messageIDs);
						
		}
		else {
		    if($saveString == 'HM') return new HausmeisterRecipient();
			else return new UnknownRecipient();
		}
	}
	
	public function getMissingNames() {
	    return[];
	}
	
	public static function getAllInstances() {
	    return [new HausmeisterRecipient()];
	}
}

