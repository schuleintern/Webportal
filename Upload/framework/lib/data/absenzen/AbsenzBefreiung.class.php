<?php 


class AbsenzBefreiung {
	private $data;
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function isPrinted() {
		return $this->data['befreiungPrinted'] > 0;
	}
	
	public function getLehrer() {
		return $this->data['befreiungLehrer'];
	}
	
	public function getUhrzeit() {
		return $this->data['befreiungUhrzeit'];
	}
	
	public function setPrinted() {
		DB::getDB()->query("UPDATE absenzen_befreiungen SET befreiungPrinted=1 WHERE befreiungID='" . $this->data['befreiungID'] . "'");
	}
	
	public function delete() {
		DB::getDB()->query("DELETE FROM absenzen_befreiungen WHERE befreiungID='" . $this->data['befreiungID'] . "'");
	}
	
}



?>