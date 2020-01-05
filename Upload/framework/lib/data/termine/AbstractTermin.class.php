<?php

abstract class AbstractTermin {
	
	protected $data;
	
	protected $table = "";
	
	private $deleteable = true;
		
	protected function __construct($data) {
		$this->data = $data;
	}
	
	public function getID() {
		return $this->data['eintragID'];
	}
	
	public function getTitle() {
		if($this->isWholeDay()) return $this->data['eintragTitel'];
		else return $this->getUhrzeitStart() . " Uhr: " . $this->data['eintragTitel'];
	}
	
	public function getTitleRaw() {
	    return $this->data['eintragTitel'];
	}
	
	public function getDatumStart() {
		return $this->data['eintragDatumStart'];
	}
	
	public function getDatumEnde() {
		return $this->data['eintragDatumEnde'];
	}
		
	public function isWholeDay() {
		return $this->data['eintragIsWholeDay'];
	}
	
	/**
	 * Formatierter Eintragzeitpunkt
	 * @see functions::makeDateFromTimestamp()
	 * @return string
	 */
	public function getEintragZeitpunkt() {
	    if(DB::getSettings()->getBoolean('datenschutz-kein-eintragzeitpunkt')) return 'n/a';
		return functions::makeDateFromTimestamp($this->data['eintragEintragZeitpunkt']);
	}
	
	public function getOrt() {
		return $this->data['eintragOrt'];
	}
	

	public function getKommentar() {
		return $this->data['eintragKommentar'];
	}
	
	public function getUhrzeitStart() {
		return $this->data['eintragUhrzeitStart'];
	}
	
	public function getUhrzeitEnde() {
		return $this->data['eintragUhrzeitEnde'];
	}
	
	public function getCreatorName() {
		$user = functions::getDisplayNameFromUserID($this->data['eintragUser']);
		return $user;
	}
	
	public function getCreatorUserID() {
	    return $this->data['eintragUser'];
	}
	
	/**
	 * 
	 * @return AbstractKalenderKategorie
	 */
	public function getKategorie() {
	    return null;
	}
	
	public function canDelete() {
	    return $this->deleteable;
	}
	
	public function setNotDeleteAble() {
	    $this->deleteable = false;
	}

    /**
     * @return string|null
     */
	public function getColor() {
	    return null;
    }
	
}

