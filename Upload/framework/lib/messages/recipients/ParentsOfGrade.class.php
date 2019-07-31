<?php

/**
 * Eltern einer Klasse
 * @author Christian
 *
 */
class ParentsOfGrade extends MessageRecipient {
	/**
	 * 
	 * @var klasse Klasse
	 */
	private $grade;
	
	/**
	 * 
	 * @param klasse $grade
	 */
	public function __construct($grade, $messageIDs = []) {
		$this->grade = $grade;
		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getSaveString() {
		return 'PG:' . $this->grade->getKlassenName();
	}

	public function getDisplayName() {
		return 'Eltern der Klasse ' . $this->grade->getKlassenName();
	}
	
	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString,0,3) == 'PG:';
	}
	public function getRecipientUserIDs() {
		$schueler = $this->grade->getSchueler(false);
		
		$userIDs = [];
		for($i = 0; $i < sizeof($schueler); $i++) {
			$parentsUsers = $schueler[$i]->getParentsUsers();
			
			for($u = 0; $u < sizeof($parentsUsers); $u++) {
				$userIDs[] = $parentsUsers[$u]->getUserID();
			}
		}
		
		return $userIDs;
	}
	
	public static function getInstanceForSaveString($saveString) {
		
		if(strpos($saveString, "[") > 0) {
			
			$klasse = substr($saveString, 0, strpos($saveString, "["));
			$grade = str_replace("PG:","",$klasse);
			
			
			$grade = klasse::getByName($grade);
			
			

			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			if($grade != null) return new ParentsOfGrade($grade, $messageIDs);
			
			else return new UnknownRecipient();
			
		}
		else {
			$grade = substr($saveString,strlen('PG:'));
			
			$grade = klasse::getByName($grade);
			
			
			if($grade != null) return new ParentsOfGrade($grade);
			else return new UnknownRecipient();
		}
	}
	
	public function getMissingNames() {
		$schueler = $this->grade->getSchueler(false);
		
		$names = [];
		for($i = 0; $i < sizeof($schueler); $i++) {
			if(sizeof($schueler[$i]->getParentsUsers()) == 0) $names[] = $schueler[$i]->getCompleteSchuelerName();
		}
		
		return $names;
	}
	
	public static function getAllInstances() {
		$klassen = klasse::getAllKlassen();
		
		$all = [];
		
		for($i = 0; $i < sizeof($klassen); $i++) {
			$all[] = new ParentsOfGrade($klassen[$i]);
		}
		
		return $all;
	}
	
	public static function getOnly($grades) {
		
		$all = [];
		
		for($g = 0; $g < sizeof($grades); $g++) {
			$klassen = klasse::getByStundenplanName($grades[$g]);
		
	
			$all[] = new ParentsOfGrade($klassen);
		}
		
		return $all;
	}
	
	public static function getInstancesForGrades($grades) {
        $all = [];
        
        for($i = 0; $i < sizeof($grades); $i++) {
            $grade = klasse::getByName($grades[$i]);
            if($grade != null) $all[] = new ParentsOfGrade($grade);
        }
        
        return $all;
        
	}
	
	/**
	 * 
	 * @return klasse
	 */
	public function getKlasse() {
		return $this->grade;
	}
	
	/**
	 * Gibt die Tabelle mit den gesendeten Nachrichten zur Info aus.
	 * @param Message $mainMessage
	 */
	public function getSentInfoTable($mainMessage) {
	    
	    $htmlConfirmation = "";
	    
	    $messages = $this->getSentMessagesWithThisRecipient();
	    
	    $questions = $mainMessage->getQuestions();
	    
	    
	    $schueler = $this->grade->getSchueler();
	    
	    $htmlConfirmation .= "<tr><th>Schüler</th><th>Elternempfänger</th>";
	    
	    if($mainMessage->needConfirmation()) $htmlConfirmation .= "<th>Empfangsbestätigung</th>";
	    
	    
	    
	    for($q = 0; $q < sizeof($questions); $q++) {
	        $htmlConfirmation .= "<th>" . $questions[$q]->getQuestionText() . "</th>";
	    }
	    
	    
	    for($i = 0; $i < sizeof($schueler); $i++) {
	        // $parents = $schueler[$i]->getElternEMail();
	        
	        $parents = $schueler[$i]->getParentsUsers();
	        
	        $rowspan = sizeof($parents) > 0 ? sizeof($parents) : 1;
	        
	        $htmlConfirmation .= "<tr><td rowspan=\"$rowspan\"><b>" . $schueler[$i]->getCompleteSchuelerName() . "</b><br /><small>(Eltern)</small></td>";
	        
	        
	        for($p = 0; $p < sizeof($parents); $p++) {
	            $found = false;
	            
	            if($p > 0) $htmlConfirmation .= "<tr>";
	            
	            for($m = 0; $m < sizeof($messages); $m++) {
	                if($messages[$m]->getUser() != null) {
	                    if($messages[$m]->getUser()->getUserID() == $parents[$p]->getUserID()) {
	                        $found = true;
	                        $htmlConfirmation .= "<td>" . $messages[$m]->getUser()->getDisplayName() . "<br /><small>" . $messages[$m]->getUser()->getUserName() . "</small></td><td>" . (($messages[$m]->isConfirmed()) ? ("<label class=\"label label-success\">Empfang bestätigt</label>") : ("<label class=\"label label-danger\">Empfang nicht bestätigt</label>")) . "</td>";
	                        
	                        
	                        for($q = 0; $q < sizeof($questions); $q++) {
	                            
	                            $answers = $questions[$q]->getAllAnswers();
	                            
	                            $htmlConfirmation .= "<td>";
	                            
	                            $data = null;
	                            
	                            for($a = 0; $a < sizeof($answers); $a++) {
	                                if($answers[$a]->getMessageID() == $messages[$m]->getID()) {
	                                    $data = $answers[$a]->getAnswerData();
	                                }
	                            }
	                            
	                            if($data == null) {
	                                $htmlConfirmation .= "<i>Keine Antwort</i>";
	                            }
	                            else {
	                                if($questions[$q]->isBooleanQuestion()) {
	                                    if($data == 1) {
	                                        $htmlConfirmation .= "Ja";
	                                    }
	                                    else {
	                                        $htmlConfirmation .= "Nein";
	                                    }
	                                }
	                                
	                                if($questions[$q]->isFileQuestion()) {
	                                    $upload = FileUpload::getByID($data);
	                                    if($upload != null) {
	                                        $htmlConfirmation .= "<a href=\"" . $upload->getURLToFile(true) . "\">" . $upload->getFileName() . "</a>";
	                                    }
	                                }
	                                
	                                if( $questions[$q]->isTextQuestion()) {
	                                    $htmlConfirmation .= $data;
	                                }
	                                
	                                
	                                if( $questions[$q]->isNumberQuestion()) {
	                                    $htmlConfirmation .= $data;
	                                }
	                            }
	                            
	                            
	                            $htmlConfirmation .= "</td>";
	                            
	                        }
	                    }
	                }
	            }
	            
	            if(!$found) {
	                
	                
	                
	                $htmlConfirmation .= "<td><i>Nicht empfangen</i></td>";
	                
	                if($mainMessage->needConfirmation()) $htmlConfirmation .= "<td>&nbsp;</td>";
	                
	                for($q = 0; $q < sizeof($questions); $q++) {
	                    $htmlConfirmation .= "<td>&nbsp;</td>";
	                }
	            }
	            
	            $htmlConfirmation .= "</tr>";
	        }
	        
	    }
	    
	    /**
	    
	    for($m = 0; $m < sizeof($messages); $m++) {
	        
	        if($messages[$m]->getUser() != null) {
	            
	            $htmlConfirmation .= "<tr><td>++" . $messages[$m]->getUser()->getDisplayName() . "<br /><small>" . $messages[$m]->getUser()->getUserName() . "</small></td><td>" . (($messages[$m]->isConfirmed()) ? ("<label class=\"label label-success\">Empfang bestätigt</label>") : ("<label class=\"label label-danger\">Empfang nicht bestätigt</label>")) . "</td>";
	        }
	        else {
	            $htmlConfirmation .= "<tr><td><i>Unbekannter Empfänger</td><td>" . (($messages[$m]->isConfirmed()) ? ("<label class=\"label label-success\">Empfang bestätigt</label>") : ("<label class=\"label label-danger\">Empfang nicht bestätigt</label>")) . "</td>";
	            
	        }
	        
	        for($q = 0; $q < sizeof($questions); $q++) {
	            
	            $answers = $questions[$q]->getAllAnswers();
	            
	            $htmlConfirmation .= "<td>";
	            
	            $data = null;
	            
	            for($a = 0; $a < sizeof($answers); $a++) {
	                if($answers[$a]->getMessageID() == $messages[$m]->getID()) {
	                    $data = $answers[$a]->getAnswerData();
	                }
	            }
	            
	            if($data == null) {
	                $htmlConfirmation .= "<i>Keine Antwort</i>";
	            }
	            else {
	                if($questions[$q]->isBooleanQuestion()) {
	                    if($data == 1) {
	                        $htmlConfirmation .= "Ja";
	                    }
	                    else {
	                        $htmlConfirmation .= "Nein";
	                    }
	                }
	                
	                if($questions[$q]->isFileQuestion()) {
	                    $upload = FileUpload::getByID($data);
	                    if($upload != null) {
	                        $htmlConfirmation .= "<a href=\"" . $upload->getURLToFile(true) . "\">" . $upload->getFileName() . "</a>";
	                    }
	                }
	                
	                if( $questions[$q]->isTextQuestion()) {
	                    $htmlConfirmation .= $data;
	                }
	                
	                
	                if( $questions[$q]->isNumberQuestion()) {
	                    $htmlConfirmation .= $data;
	                }
	            }
	            
	            
	            $htmlConfirmation .= "</td>";
	            
	        }
	        
	        
	        $htmlConfirmation .= "</tr>";
	        
	    }
	    **/
	    
	    return $htmlConfirmation;
	}
}

