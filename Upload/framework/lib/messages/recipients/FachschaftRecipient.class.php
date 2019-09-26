<?php


class FachschaftRecipient extends MessageRecipient {
	
    /**
     * 
     * @var string
     */
	private $fach = '';
	
	
	/**
	 * Kein Objekt von Außerhalb
	 */
	public function __construct($fach, $messageIDs = []) {
		$this->fach = $fach;
		
		if(sizeof($messageIDs) > 0) {
			$this->isSentRecipient = true;
			
			parent::__construct($messageIDs);
			
		}
	}
	
	public function getSaveString() {
		return 'teacher_fach_' . $this->fach;
	}
	
	public function getDisplayName() {
		
		$lehrer = $this->getLehrerRecipients();
		
		$lehrerText = [];
		
		for($i = 0; $i < sizeof($lehrer); $i++) {
			$lehrerText[] = $lehrer[$i]->getKuerzel();
		}
		
		return 'Fachschaft ' . $this->fach . " (" . implode(", ",$lehrerText) . ")";
	}
	
	public function getRecipientUserIDs() {
		$userIDs = [];
	
		$lehrer = $this->getLehrerRecipients();
		
		for($i = 0; $i < sizeof($lehrer); $i++) {
		    if($lehrer[$i]->getUserID() > 0) $userIDs[] = $lehrer[$i]->getUserID();
		}
		
		return $userIDs;
	}
	
	/**
	 * 
	 * @return lehrer[]
	 */
	private function getLehrerRecipients() {
		$allLehrer = [];
		
		
		$fach = fach::getByKurzform($this->fach);
		
		if($fach != null) {
		    $teachers = $fach->getFachLehrer();
		    
		    for($i = 0; $i < sizeof($teachers); $i++) {
		        
		        $lehrer = $teachers[$i];
		        if($lehrer != null) {
		            $allLehrer[] = $lehrer;
		        }
		    }
		    
		    return $allLehrer;
		    
		}
		else return [];
		

	}
	
	public static function getAllInstances() {
		$faecher = fach::getAll();
		
		$all = [];
		for($i = 0; $i < sizeof($faecher); $i++) {
			$all[] = new FachschaftRecipient($faecher[$i]->getKurzform());
		}
		
		return $all;
		
		
	}

	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		
		return substr($saveString,0,strlen('teacher_fach_')) == 'teacher_fach_';
	}

	public function getMissingNames() {
		$alle = $this->getLehrerRecipients();
		
		$missing = [];
		
		for($i = 0; $i < sizeof($alle); $i++) {
			if($alle[$i]->getUserID() > 0) {
			
			}
			else {
				$missing[] = $alle[$i]->getDisplayNameMitAmtsbezeichnung();
			}
		}
		
		
		return $missing;
	}
	
	public static function getInstanceForSaveString($saveString) {
		
		
		if(strpos($saveString, "[") > 0) {
			
			$fach = substr($saveString, 0, strpos($saveString, "["));
			$fach = str_replace("teacher_fach_","",$fach);
						
			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			return new FachschaftRecipient($fach, $messageIDs);
		}
		
		else {
			$fach = substr($saveString,strlen('teacher_fach_'));
			return new FachschaftRecipient($fach);
		}
		
		
	}
	

}

