<?php


class AllTeacherButSchulleitungRecipient extends MessageRecipient {

	public function __construct($messageIDs = []) {
		parent::__construct($messageIDs);
		if(sizeof($messageIDs) > 0) $this->isSentRecipient = true;
	}
	
	public function getSaveString() {
		return 'all_teacher_but_sl';
	}
	
	public function getDisplayName() {
		return 'Alle Lehrer (ohne Schulleitung)';
	}
	
	public function getRecipientUserIDs() {
		$userIDs = [];

        /** @var lehrer[] $lehrer */
		$lehrer = lehrer::getAll();
		for($i = 0; $i < sizeof($lehrer); $i++) {
			if($lehrer[$i]->getUserID() > 0) {
                if(!$lehrer[$i]->isSchulleitung())  $userIDs[] = $lehrer[$i]->getUserID();
            }
		}
		
		return $userIDs;
	}
	
	public static function getAllInstances() {
		return [new AllTeacherButSchulleitungRecipient()];
	}

	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
        if($saveString == "all_teacher_but_sl") return true;

        if(strpos($saveString, "[") > 0) {
            return substr($saveString,0,strpos($saveString, "[")) == 'all_teacher_but_sl';
        }


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
		if(substr($saveString,0,strlen('all_teacher_but_sl')) == 'all_teacher_but_sl') {
			if(strpos($saveString, "[") > 0) {
			
				$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			}
			else $messageIDs = [];
			
			return new AllTeacherButSchulleitungRecipient($messageIDs);
		}
		else return new UnknownRecipient();
	}


}

