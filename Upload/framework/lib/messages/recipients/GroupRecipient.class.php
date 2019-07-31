<?php


class GroupRecipient extends MessageRecipient {
	
	private $group = '';
	
	
	/**
	 * Kein Objekt von Außerhalb
	 */
	public function __construct($groupName, $messageIDs = []) {
		$this->group = usergroup::getGroupByName($groupName);
		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getSaveString() {
		return 'group_' . $this->group->getName();
	}
	
	public function getDisplayName() {
	    if(substr($this->group->getName(),0,strlen("group_")) == "group_") return "Gruppe " . substr($this->group->getName(),strlen("group_"));
		return 'Gruppe ' . $this->group->getName();
	}
	
	public function getRecipientUserIDs() {
		$userIDs = [];
		
		if($this->group == null) return [];
		
		$users = $this->group->getMembers();
		
		for($i = 0; $i < sizeof($users); $i++) $userIDs[] = $users[$i]->getUserID();
		
		return $userIDs;
	}
	
	
	/**
	 * 
	 * @return GroupRecipient[]
	 */
	public static function getAllInstances() {
		$groups = usergroup::getAllOwnGroups();
		
		$myContactGroups = [];
		
		for($i = 0; $i < sizeof($groups); $i++) {
		    if($groups[$i]->isMessageRecipient()) {
		        $suc = false;
		        
		        if(DB::getSession()->isAdmin()) {
                    $suc = true;
		        }
		        
		        if(DB::getSession()->isMember('Webportal_Elternmail')) $suc = true;
		        
		        if(DB::getSession()->isEltern() && $groups[$i]->canContactByParents()) $suc = true;
		        
		        if(DB::getSession()->isTeacher() && $groups[$i]->canContactByTeacher()) $suc = true;
		        
		        if(DB::getSession()->isPupil() && $groups[$i]->canContactByPupil()) $suc = true;
		        
		        // if($groups[$i]->isMember(DB::getSession()->getUser())) $suc = true;

		        
		        if($suc) {
		            $myContactGroups[] = new GroupRecipient($groups[$i]->getName());
		        }
		    }
		}
		
		
		return $myContactGroups;
	}
	
	/**
	 * 
	 * @param String[] $klassen
	 * @return KlassenteamRecipient[]
	 */
	public static function getAllInstancesForGrade($klassen) {
		$klasse = stundenplandata::getCurrentStundenplan()->getAll('grade');
		
		$all = [];
		for($i = 0; $i < sizeof($klasse); $i++) {
			for($o = 0; $o < sizeof($klassen); $o++) {
				if($klassen[$o] == $klasse[$i]) {
					$all[] = new KlassenteamRecipient($klasse[$i]);
					break;
				}
			}	
		}
		
		return $all;
	}

	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		
		return substr($saveString,0,strlen('group_')) == 'group_';
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
			
			$klasse = substr($saveString, 0, strpos($saveString, "["));
			$fach = str_replace("group_","",$klasse);
			
			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			return new GroupRecipient($klasse, $messageIDs);
			
		}
		else {
			$klasse = substr($saveString,strlen('group_'));
			
			return new GroupRecipient($klasse);
		}
	}


}

