<?php

/**
 * Nicht vollständig implementiert.
 * @deprecated
 */
class CronNextCloud extends AbstractCron {
    
    /**
     * Anzahl der durchgeführten Aktionen
     * @var integer
     */
    private $actionsDone = 0;
    
    private $logFile = "";
	
	public function __construct() {
	    
	}

	public function execute() {
	    
	    // Alle Schüler vorhanden?
	    
	    // Nur, wenn in den Haupteinstellungen aktiviert.
	    
	    if(!DB::getGlobalSettings()->enableNextCloud) {
	        return;
	    }
	   	    

	    if(DB::getSettings()->getBoolean('nextcloud-schueler')) {
	        
	        NextCloudApi::addGroup("Schueler");
	        
	        $currentSchueler = schueler::getAll();
	        
	        $users = [];
	        
	        for($i = 0; $i < sizeof($currentSchueler); $i++) {
	            $user = $currentSchueler[$i]->getUser();
	            if($user != null && $user->getUserID() > 0) {
	                $users[] = $user;
	            }
	        }
	        
	        $this->syncUsersWithNextCloudGroup($users, "Schueler", true); // Nicht mehr vorhandene Schüler löschen
	        
	        
	        if($this->checkActionCount()) return;
	        
	        // Klassenweise Synchronisieren
	        
	        $klassen = klasse::getAllKlassen();
	        
	        for($i = 0; $i < sizeof($klassen); $i++) {
	            $klassenName = $klassen[$i]->getKlassenName();
	            
	            
	            $schueler = $klassen[$i]->getSchueler(false);
	            
	            $users = [];
	            
	            for($s = 0; $s < sizeof($schueler); $s++) {
	                $user = $schueler[$s]->getUser();
	                if($user != null && $user->getUserID() > 0) {
	                    $users[] = $user;
	                }
	            }
	            
	            $groupName = "Klasse " . $klassenName;
	            
	            $groupName = str_replace(" ", "_", $groupName);
	            $groupName = str_replace(" ", "-", $groupName);
	            
	            NextCloudApi::addGroup($groupName);
	            
	            $this->syncUsersWithNextCloudGroup($users, $groupName);
	            
	            if($this->checkActionCount()) return;
	        }
	        
	    }
	    
	    if($this->checkActionCount()) return;
	    
	    if(DB::getSettings()->getBoolean('nextcloud-lehrer')) {
	        
	        NextCloudApi::addGroup("Lehrer");
	        
	        $currentLehrer = lehrer::getAll();
	        
	        $users = [];
	        
	        for($i = 0; $i < sizeof($currentLehrer); $i++) {
	            $user = $currentLehrer[$i]->getUser();
	            if($user != null && $user->getUserID() > 0) {
	                $users[] = $user;
	            }
	        }
	        
	        $this->syncUsersWithNextCloudGroup($users, "Lehrer");
	        
	        
	        if($this->checkActionCount()) return;
	        
	        
	        $faecher = fach::getAll();
	        
	        for($f = 0; $f < sizeof($faecher); $f++) {
	            $lehrer = $faecher[$f]->getFachLehrer();
	            
	            $users = [];
	            
	            for($l = 0; $l < sizeof($lehrer); $l++) {
	                $user = $lehrer[$l]->getUser();
	                if($user != null && $user->getUserID() > 0) {
	                    $users[] = $user;
	                }
	            }
	            
	            
	            $groupName = "Lehrer " . $faecher[$f]->getLangform();
	            
	            $groupName = str_replace(" ", "_", $groupName);
	            $groupName = str_replace("/", "-", $groupName);
	            
	            NextCloudApi::addGroup($groupName);
	            
	            $this->syncUsersWithNextCloudGroup($users, $groupName);
	            
	            if($this->checkActionCount()) return;
	        }
	        
	    }
	    
	    
	    
	    
	    
	}
	
	
	/**
	 * 
	 * @param user[] $users
	 * @param String $nextcloudGroupName
	 */
	private function syncUsersWithNextCloudGroup($users, $nextcloudGroupName, $deleteUsers = false) {
	    
	    // Aktuell
	    
	    $cloudUsers = NextCloudApi::getMembersOfGroup($nextcloudGroupName);
        $cloudUsersChecked = [];
	    
	    // Wer muss angelegt werden?
	    
	    for($i = 0; $i < sizeof($users); $i++) {
	        if(!in_array($users[$i]->getUserName(), $cloudUsers)) {
	            // Anlegen
	            
	            $this->logFile .= $users[$i]->getUserName() . " wird angelegt.\r\n";
	            NextCloudApi::createUser($users[$i]->getUserName(), $users[$i]->getDisplayName(), $users[$i]->getEMail(), md5(rand()));
	            
	            $this->logFile .= $users[$i]->getUserName() . " wird der Gruppe $nextcloudGroupName hinzugefügt.\r\n";
	            NextCloudApi::addUserToGroup($users[$i]->getUserName(), $nextcloudGroupName);
	            
	            if($this->checkActionCount()) return;
	        }
	        else {
	            $cloudUsersChecked[] = $users[$i]->getUserName();
	        }
	    }
	    
	    // Wer muss entfernt werden?
	    
	    for($i = 0; $i < sizeof($cloudUsers); $i++) {
	        if(in_array($cloudUsers[$i], $cloudUsersChecked)) {
	            NextCloudApi::removeUserFromGroup($cloudUsers[$i], $nextcloudGroupName);
	            
	            if($deleteUsers) {
	                NextCloudApi::deleteUser($cloudUsers[$i]);
	            }
	            
	            if($this->checkActionCount()) return;
	        }
	    }
	    
	}
	
	
	private function checkActionCount() {
	    $this->actionsDone++;
	    
	    return $this->actionsDone > 10;
	}
	
	public function getName() {
		return "NextCloud Synchronisation mit lokaler Datenbank und Einstellungen";
	}
	
	public function getDescription() {
		return "";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
        return [
            'success' => true,
            'resultText' => $this->logFile
        ];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 21600;		// Zwei mal am Tag ausführen
	}
	
	public function onlyExecuteSeparate() {
	    return true;
	}
}



?>