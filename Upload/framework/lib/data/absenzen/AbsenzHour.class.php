<?php 

class AbsenzHour {
	private $stunde = 0;
	
	private $isEntschuldigt = false;
	
	private $absenzID = 0;
	
	public function __construct($data) {
		$this->absenzID = $data['absenzID'];
		$this->stunde = $data['absenzStunde'];
		$this->isEntschuldigt = $data['absenzStundeEntschuldigt'] == 1;
	}
	
	public function getStunde() {
		return $this->stunde;
	}
	
	public function isEntschuldigt() {
		return $this->isEntschuldigt;
	}
	
	public function markAsEntschuldigt() {
		DB::getDB()->query("UPDATE absenzen_absenzen_stunden SET absenzStundeEntschuldigt=1 WHERE absenzID='" . $this->absenzID . "' AND absenzStunde='" . $this->stunde . "'");
	}
}


?>