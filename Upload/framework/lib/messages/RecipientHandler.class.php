<?php

class RecipientHandler {
	private static $knownRecipientClasses = [
		'AllTeacherRecipient',
		'PupilsOfGrade',
		'TeacherRecipient',
		'FachschaftRecipient',
		'KlassenteamRecipient',
		'PupilRecipient',
		'ParentsOfGrade',
		'ParentRecipient',
		'UserRecipient',
	    'HausmeisterRecipient',
	    'SchulleitungRecipient',
	    'PersonalratRecipient',
	    'VerwaltungRecipient',
	    'GroupRecipient',
	    'KlassenleitungRecipient',
	    'PupilsOfClassRecipient',
	    'ParentsOfPupilsOfClassRecipient'
	];
	
	/**
	 * 
	 * @var MessageRecipient[] Empfänger
	 */
	private $recipients = [];
	
	/**
	 * Erstellt einen neuen Recipienthandler
	 * @param $saveString bisheriger SaveString
	 */
	public function __construct($saveString) {
		$data = explode(";",$saveString);
		for($i = 0; $i < sizeof($data); $i++) {
			if($data[$i] != "") $this->addRecipientFromSaveString($data[$i]);
		}
		
	}
	
	/**
	 * Empfänger aus SaveString hinzufügen
	 * @param unknown $saveString
	 */
	public function addRecipientFromSaveString($saveString) {
		$added = false;
		
		for($i = 0; $i < sizeof(self::$knownRecipientClasses); $i++) {
			/**
			 * 
			 * @var MessageRecipient $class
			 */
		    $class = self::$knownRecipientClasses[$i];
			if($class::isSaveStringRecipientForThisRecipientGroup($saveString)) {
				$this->recipients[] = $class::getInstanceForSaveString($saveString);
				$added = true;
				break;
			}
		}
		
		if(!$added) $this->recipients[] = new UnknownRecipient();
	}
	
	public static function getRecipientFromSaveString($saveString) {
	    $object = null;
	    
	    for($i = 0; $i < sizeof(self::$knownRecipientClasses); $i++) {
	        /**
	         *
	         * @var MessageRecipient $class
	         */
	        $class = self::$knownRecipientClasses[$i];
	        
	        if($class::isSaveStringRecipientForThisRecipientGroup($saveString)) {
	            $object = $class::getInstanceForSaveString($saveString);
	            break;
	        }
	    }
	    
	    if($object == null) return new UnknownRecipient();
	    
	    return $object;
	}
	
	/**
	 * 
	 * @param MessageRecipient $recipient
	 */
	public function addRecipient($recipient) {
	    $this->recipients[] = $recipient;
	}
	
	/**
	 * Kompletter SaveString (getrennt mit ";" für das Formular.)
	 * @return string
	 */
	public function getSaveCompleteSaveString() {
		$data = [];
		for($i = 0; $i < sizeof($this->recipients); $i++) {
			if($this->recipients[$i] != null) {
				$saveString = $this->recipients[$i]->getSaveString();
				if($saveString != "") $data[] = $saveString;
			}
		}
		
		return implode(";",$data);
	}
	
	/**
	 * Entfernt den Savestring
	 * @param unknown $saveString
	 */
	public function removeSaveString($saveString) {
		for($i = 0; $i < sizeof($this->recipients); $i++) {
			if($this->recipients[$i] != null) {
				if($this->recipients[$i]->getSaveString() == $saveString) {
					$this->recipients[$i] = null;
				}
			}
		}
	}
	
	/**
	 * Alle Empfänger
	 * @return MessageRecipient[]
	 */
	public function getAllRecipients() {
		$all = [];
		
		for($i = 0; $i < sizeof($this->recipients); $i++) {
			if($this->recipients[$i] != null) {
				$all[] = $this->recipients[$i];
			}
		}
		
		return $all;
	}
	
}

