<?php

class UserRecipient extends MessageRecipient {
	
	/**
	 * 
	 * @var user
	 */
	private $user;
	
	public function __construct($user, $messageIDs = []) {
		$this->user = $user;
		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getDisplayName() {
		return $this->user->getDisplayName();
	}
	
	public function getSaveString() {
		return 'U:' . $this->user->getUserID();
	}
	
	public function getRecipientUserIDs() {
		return [$this->user->getUserID()];
	}
	
	public function getMissingNames() {
		return [];
	}
	
	public static function getAllInstances() {
		return [];
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString, 0,2) == "U:";
	}
	
	public static function getInstanceForSaveString($saveString) {
		if(strpos($saveString, "[") > 0) {
			
			$klasse = substr($saveString, 0, strpos($saveString, "["));
			$userID = str_replace("U:","",$klasse);
			
			
			$user = user::getUserByID($userID);
			
			
			
			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			
			if($user != null)	return new UserRecipient($user, $messageIDs);
			else return new UnknownRecipient();
			
		}
		else {
			$user = substr($saveString,strlen('U:'));
			$user = user::getUserByID(substr($saveString, 2));
			
			
			if($user != null)	return new UserRecipient($user);
			else return new UnknownRecipient();
			
		}
		

	}

}