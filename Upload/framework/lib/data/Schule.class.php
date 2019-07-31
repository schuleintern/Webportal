<?php

class Schule {
	private $data;
	
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getID() {
		return $this->data['schuleID'];
	}
	
	public function getSchuleNummer() {
		return $this->data['schuleNummer'];
	}
	
	public function getSchuleArt() {
		return $this->data['schuleArt'];
	}
	
	public function getName() {
		return $this->data['schuleName'];
	}
	
	/**
	 * 
	 * @param int $id
	 * @return Schule|NULL
	 */
	public static function getByID($id) {
		$data = DB::getDB()->query_first("SELECT * FROM schulen WHERE schuleID='" . DB::getDB()->escapeString($id) . "'");
	
		if($data['schuleID'] > 0) {
			return new Schule($data);
		}
		
		return null;
	}
	
	
}

