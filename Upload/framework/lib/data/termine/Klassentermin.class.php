<?php


class Klassentermin {

	private $data;
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getID() {
		return $this->data['eintragID'];
	}
	
	public function getTitle() {
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
	
	public function setDatumStart($newDate) {
	    DB::getDB()->query("UPDATE kalender_klassentermin SET eintragDatumStart = '" . DB::getDB()->escapeString($newDate) . "' WHERE eintragID='" . $this->getID() . "'");
	}
	
	public function setDatumEnde($newDate) {
	    DB::getDB()->query("UPDATE kalender_klassentermin SET eintragDatumEnde = '" . DB::getDB()->escapeString($newDate) . "' WHERE eintragID='" . $this->getID() . "'");
	}
	
	/**
	 * Formatierter Eintragzeitpunkt
	 * @see functions::makeDateFromTimestamp()
	 * @return string
	 */
	public function getEintragZeitpunkt() {
		return functions::makeDateFromTimestamp($this->data['eintragEintragZeitpunkt']);
	}
	
	public function getOrt() {
		return $this->data['eintragOrt'];
	}
	
	
	public function getKlassen() {
		$klassen = [];
	
		$kl = explode(",",$this->data['eintragKlassen']);
		for($i = 0; $i < sizeof($kl); $i++) {
			if($kl[$i] != "") $klassen[] = $kl[$i];
		}
	
		return $klassen;
	}
	
	public function getBetrifft() {
		return $this->data['eintragBetrifft'];
	}
	
	public function getLehrer() {
		return $this->data['eintragLehrer'];
	}
	
	public function getStunden() {
		$stunden = [];
	
		$s = explode(",",trim($this->data['eintragStunden']));
		for($i = 0; $i < sizeof($s); $i++) {
			if($s[$i] != "" && functions::isNumber($s[$i])) $stunden[] = $s[$i];
		}
	
		return $stunden;
	}
	

	
  /**
   *
   * @return Klassentermin[] Termine
   * @param String[] $classes Liste der Klassen Wenn der Array leer ist, dann werden alle Termine ausgelesen
   * @param String $afterDate Datum nach dem die Termine ausgelesen werden sollen (SQL Date)
   */
  public static function getByClass($classes = [], $afterDate = "", $beforeDate = "") {
  	$where = "";
    	 
//    	Debugger::debugObject($classes,1);
  	if(sizeof($classes) > 0) {
  		$where = " WHERE (";
  		
  		for($i = 0; $i < sizeof($classes); $i++) {
  			if($i > 0) $where .= " OR ";
  			$where .= " eintragKlassen LIKE '%" . $classes[$i] . "%' ";
  		}
  		
  		$where .= ")";
  		
  	}
  	 
  	if($afterDate != "") {
  		if($where != "") $where .= " AND ";
  		else $where .= " WHERE ";
  
  		$where .= " eintragDatumStart >= '" . $afterDate . "' ";
  	}
  	
  	if($beforeDate != "") {
  		if($where != "") $where .= " AND ";
  		else $where .= " WHERE ";
  	
  		$where .= " eintragDatumStart <= '" . $beforeDate . "' ";
  	}
  	 
  	$all = [];
  	 
  	$data = DB::getDB()->query("SELECT * FROM kalender_klassentermin $where ORDER BY eintragDatumStart ASC");
  	while($d = DB::getDB()->fetch_array($data)) {
  		$all[] = new Klassentermin($d);
  	}
  	
   	return $all;
  }
  
  public static function getBayTeacher($teacher, $afterDate = "", $beforeDate = "") {
  	$where = " WHERE eintragLehrer LIKE '" . $teacher . "'";
  
  	if($afterDate != "") {
  		$where .= " AND eintragDatumStart >= '" . $afterDate . "' OR (eintragDatumStart <= '$afterDate' AND eintragDatumEnde >= '$afterDate')";
  	}
  	
  	if($beforeDate != "") {
  		$where .= " AND eintragDatumStart <= '" . $beforeDate . "' OR (eintragDatumStart >= '$beforeDate' AND eintragDatumEnde <= '$beforeDate')";
  	}
  
  	$all = [];
  	$data = DB::getDB()->query("SELECT * FROM kalender_klassentermin $where ORDER BY eintragDatumStart ASC");
  	while($d = DB::getDB()->fetch_array($data)) {
  		$all[] = new Klassentermin($d);
  	}
  
  	return $all;
  }
  
  public static function getByID($id) {
      $data = DB::getDB()->query_first("SELECT * FROM kalender_klassentermin WHERE eintragID='" . intval($id) ."'");
      if($data['eintragID'] > 0) return new Klassentermin($data);
      else return null;
  }
}

