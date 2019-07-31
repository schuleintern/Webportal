<?php 

class SchuelerTelefonnummer {
	private $data = array();
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getNummer() {
		return $this->data['telefonNummer'];
	}
	
	public function getTypAsText() {
		switch($this->data['telefonTyp']) {
			case 'telefon':
				return "Festnetz";
				
			case 'fax':
				return "Fax";
			
			case 'mobiltelefon':
				return "Handy";
			
			default:
				return "Andere Nummer";
		}
	}
	
	public function getTyp() {
		return $this->data['telefonTyp'];
	}
	
	public function getKontaktTyp() {
		return $this->data['kontaktTyp'];
	}
	
	public function getAdresseID() {
		return $this->data['adresseID'];
	}
	
}


?>