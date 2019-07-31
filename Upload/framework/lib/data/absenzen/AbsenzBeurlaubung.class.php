<?php 


class AbsenzBeurlaubung {
	private $data;
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function isPrinted() {
		return $this->data['beurlaubungPrinted'] > 0;
	}

	public function setPrinted() {
		DB::getDB()->query("UPDATE absenzen_beurlaubungen SET beurlaubungPrinted=1 WHERE beurlaubungID='" . $this->data['beurlaubungID'] . "'");
	}
	
	public function delete() {
		DB::getDB()->query("DELETE FROM absenzen_beurlaubungen WHERE beurlaubungID='" . $this->data['beurlaubungID'] . "'");
	}
	
	public function isInternAbwesend() {
		return $this->data['beurlaubungIsInternAbwesend']  > 0;
	}
	
	public function getAllAbsenzen() {
		$absenzen = DB::getDB()->query("SELECT * FROM absenzen_absenzen LEFT JOIN schueler ON absenzSchuelerAsvID=schuelerAsvID WHERE absenzBeurlaubungID='" . $this->data['beurlaubungID'] . "' ORDER BY absenzDatum");
	
		$returnArray = array();
		while($a = DB::getDB()->fetch_array($absenzen)) {
			$returnArray[] = new Absenz($a);
		}
		
		return $returnArray;
	}
}



?>