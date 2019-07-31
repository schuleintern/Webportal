<?php


class AllTeacherRecipient extends MessageRecipient {
	/**
	 * Kein Objekt von Außerhalb
	 */
	public function __construct($messageIDs = []) {
		parent::__construct($messageIDs);
		if(sizeof($messageIDs) > 0) $this->isSentRecipient = true;
	}
	
	public function getSaveString() {
		return 'all_teacher';
	}
	
	public function getDisplayName() {
		return 'Alle Lehrer';
	}
	
	public function getRecipientUserIDs() {
		$userIDs = [];
	
		$lehrer = lehrer::getAll();
		for($i = 0; $i < sizeof($lehrer); $i++) {
			if($lehrer[$i]->getUserID() > 0) $userIDs[] = $lehrer[$i]->getUserID();
		}
		
		return $userIDs;
	}
	
	public static function getAllInstances() {
		return [new AllTeacherRecipient()];
	}

	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString,0,strlen('all_teacher')) == 'all_teacher';
	}

	public function getMissingNames() {
		$lehrer = lehrer::getAll();
		
		$missing = [];
		
		for($i = 0; $i < sizeof($lehrer); $i++) {
			if($lehrer[$i]->getUserID() == 0) $missing[] = $lehrer[$i]->getDisplayNameMitAmtsbezeichnung();
		}
		
		return $missing;
	}
	
	public static function getInstanceForSaveString($saveString) {
		if(substr($saveString,0,strlen('all_teacher')) == 'all_teacher') {
			if(strpos($saveString, "[") > 0) {
			
				$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			}
			else $messageIDs = [];
			
			return new AllTeacherRecipient($messageIDs);
		}
		else return new UnknownRecipient();
	}


}

