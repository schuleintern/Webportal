<?php

abstract class MessageRecipient {
	private $knownRecipientClasses = [
		'TeacherRecipient'
	];
	
	
	/**
	 * Ist der Empfänger aus einer gesendeten Nachricht?
	 * @var boolean
	 */
	protected $isSentRecipient = false;
	
	/**
	 *
	 * @var Message[]
	 */
	protected $messages = [];
	
	public function __construct($messageIDs = []) {
		
		$messageIDs = array_filter($messageIDs, function($value) { return trim($value) !== ''; });
		
		
		if(sizeof($messageIDs) > 0) {
			$data = DB::getDB()->query("SELECT * FROM messages_messages  LEFT JOIN users ON users.userID=messages_messages.messageUserID WHERE messageID IN (" . implode(",",$messageIDs) . ") ORDER BY userLastName ASC, userFirstName ASC");
			while($d = DB::getDB()->fetch_array($data)) $this->messages[] = new Message($d);
		}
		
		
	}
	
	/**
	 * @return String Anzeigename für Empfänger
	 */
	public abstract function getDisplayName();
	
	/**
	 * @return String Speicher-String für Empfänger
	 */
	public abstract function getSaveString();
	
	/**
	 * @return int[] UserIDs für den Empfang
	 */
	public abstract function getRecipientUserIDs();
	
	/**
	 * @return MessageRecipient[] alle Möglichen Objekte der Empfängerliste, für die der Benutzer Zugriff hat.
	 */
	public abstract static function getAllInstances();
	
	
	/**
	 * Überprüft, ob der angegebene Speicherstring Teil der Empfängergrupppe ist
	 * @param String $saveString
	 */
	public abstract static function isSaveStringRecipientForThisRecipientGroup($saveString);
	
	/**
	 * 
	 * @return String[] Liste der nicht elektronisch erreichbaren Empfänger (nicht in getRecipientUserIDs() enthalten.)
	 */
	public abstract function getMissingNames();
	
	/**
	 * 
	 * @param String $saveString
	 * @return MessageRecipient Empfängerobjekt
	 */	
	public abstract static function getInstanceForSaveString($saveString);
	
	public function isSent() {
		return $this->isSentRecipient;
	}
	
	
	
	/**
	 * Gibt alle Nachrichten zurück, die mit diesem Empfänger (muss ein Empfangsempfänger sein) gesendet wurden.
	 * @return Message[] Nachrichten
	 */
	public function getSentMessagesWithThisRecipient() {
		return $this->messages;
	}
	
	/**
	 * Gibt die Tabelle mit den gesendeten Nachrichten zur Info aus.
	 * @param Message $mainMessage
	 */
	public function getSentInfoTable($mainMessage) {
	    
	    $htmlConfirmation = "<tr><th>Empfänger</th>";
	    
	    if($mainMessage->needConfirmation()) {
	        $htmlConfirmation .= "<th>Empfangsbestätigung</th>";
	    }
	    
	    
	    
	    $messages = $this->getSentMessagesWithThisRecipient();
	    
	    $questions = $mainMessage->getQuestions();
	    
	    // Debugger::debugObject($questions,1);
	    
	    for($q = 0; $q < sizeof($questions); $q++) {
	        $htmlConfirmation .= "<th>" . $questions[$q]->getQuestionText() . "</th>";
	    }
	    
	    $htmlConfirmation .= "</tr>";
	    
	    for($m = 0; $m < sizeof($messages); $m++) {
	        
	        if($messages[$m]->getUser() != null) {
	            
	            $htmlConfirmation .= "<tr><td>" . $messages[$m]->getUser()->getDisplayName() . "<br /><small>" . $messages[$m]->getUser()->getUserName() . "</small></td><td>" . (($messages[$m]->isConfirmed()) ? ("<label class=\"label label-success\">Empfang bestätigt</label>") : ("<label class=\"label label-danger\">Empfang nicht bestätigt</label>")) . "</td>";
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
	    
	    return $htmlConfirmation;
	}
	
	/**
	 * Ermittelt die Prozentzahl der bestätigten Nachrichten.
	 * @return int
	 */
	public function getPercentConfirmed() {
		$total = 0;
		$confirmed = 0;
		
		for($i = 0; $i < sizeof($this->messages); $i++) {
			$total++;
			if($this->messages[$i]->isConfirmed()) $confirmed++;
		}
		
		if($total > 0) {
			return round($confirmed / $total, 2) * 100;
		}
		else return 100;
	}
	
	
	/**
	 * Ermittelt die Anzahl der gesendeten Nachrichten.
	 * @return int
	 */
	public function getTotalCountSentMessages() {
		return sizeof($this->messages);
	}
	
	/**
	 * Ermittelt die Anzahl der gesendeten Nachrichten.
	 * @return int
	 */
	public function getTotalCountSentConfirmedMessages() {
		$confirmed = 0;
		
		for($i = 0; $i < sizeof($this->messages); $i++) {
			if($this->messages[$i]->isConfirmed()) $confirmed++;
		}
		
		return $confirmed;
	}

}