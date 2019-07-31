<?php

class TeacherRecipient extends MessageRecipient {
	
	/**
	 * Klasse(n), die Lehrer im aktuellen Kontext unterrichtet.
	 * 
	 * @var String[]
	 */
	private $klasse = [];
	
	/**
	 * Fach / Fächer, die Lehrer im aktuellen Kontext unterrichtet.
	 * @var string
	 */
	private $fach = [];
	
	/**
	 * 
	 * @var lehrer lehrer
	 */
	private $teacher;
	
	/**
	 * 
	 * @param lehrer $lehrer
	 */
	public function __construct($lehrer, $messageIDs = []) {
		$this->teacher = $lehrer;
		
		if(sizeof($messageIDs) > 0) {
			parent::__construct($messageIDs);
			$this->isSentRecipient = true;
		}
	}
	
	public function getDisplayName() {
		return 'Lehrer: ' . $this->teacher->getDisplayNameMitAmtsbezeichnung();
	}
	
	public function getSaveString() {
		return 'T:' . $this->teacher->getAsvID();
	}
	
	public function getRecipientUserIDs() {
		if($this->teacher->getUserID() > 0) {
			return [$this->teacher->getUserID()];
		}
	}
	
	public function getMissingNames() {
		if($this->teacher->getUserID() == 0) {
			return [$this->teacher->getDisplayNameMitAmtsbezeichnung()];
		}
		else return [];
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getKlasse() {
		return implode(", ", $this->klasse);
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getFach() {
		return implode(", ", $this->fach);
	}
	
	/**
	 * 
	 * @return lehrer
	 */
	public function getTeacher() {
		return $this->teacher;
	}
	
	/**
	 * 
	 * @return TeacherRecipient[]
	 */
	public static function getAllInstances() {
		$all = [];
		$teachers = lehrer::getAll();
		
		for($i = 0; $i < sizeof($teachers); $i++) $all[] = new TeacherRecipient($teachers[$i]);
		
		return $all;
	}

	public static function isSaveStringRecipientForThisRecipientGroup($saveString) {
		return substr($saveString, 0,2) == "T:";
	}
	
	public static function getInstanceForSaveString($saveString) {
		if(strpos($saveString, "[") > 0) {
			
			$klasse = substr($saveString, 0, strpos($saveString, "["));
			$grade = str_replace("T:","",$klasse);
			
			
			$lehrer = lehrer::getByASVId($grade);
			
			
			
			
			// Vorhandene Nachrichten
			
			$messageIDs = explode(",",str_replace("]","",substr($saveString,strpos($saveString, "[")+1)));
			
			
			if($lehrer != null)	return new TeacherRecipient($lehrer, $messageIDs);
			else return new UnknownRecipient();
			
		}
		else {
			$lehrer = substr($saveString,strlen('T:'));
			$lehrer = lehrer::getByASVId(substr($saveString, 2));
			
			
			if($lehrer != null)	return new TeacherRecipient($lehrer);
			else return new UnknownRecipient();
			
		}
		

	}
	
	/**
	 * 
	 * @param String[] $teacherKuerzelListe
	 * @return TeacherRecipient[]
	 */
	public static function getAllInstancesForTeacherWithSujectList($teacherKuerzelListe,$grade) {
		/**
		 * 
		 * @var TeacherRecipient[]
		 */
		$myRecipients = [];
		foreach ($teacherKuerzelListe as $fach => $kuerzel) {
			$t = lehrer::getByKuerzel($kuerzel);
			
			if($t != null) {
				// Schon vorhanden?
				$vorhanden = false;
				for($i = 0; $i < sizeof($myRecipients); $i++) {
					if($myRecipients[$i]->getTeacher() == $kuerzel) {
						$myRecipients[$i]->fach[] = $fach;
						$myRecipients[$i]->klasse[] = $grade;
						$vorhanden = true;
					}
				}
				
				if(!$vorhanden) {
					$recipient = new TeacherRecipient($t);
					$recipient->fach[] = $fach;
					$recipient->klasse[] = $grade;
					
					$myRecipients[] = $recipient;
				}
			}
		}
		
		return $myRecipients;
	}


}