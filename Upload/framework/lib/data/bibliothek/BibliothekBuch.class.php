<?php


/**
 * Ein Schulbuch, zum ausleihen
 * @author Christian
 *
 */
class BibliothekBuch {
	
	private $data;
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getID() {
		return $this->data['buchID'];
	}
	
	public function getName() {
		return $this->data['buchTitel'];
	}
	
	public function getISBN() {
		return $this->data['buchISBN'];
	}
	
	public function getPreis() {
		return str_replace(".",",", $this->data['buchPreis'] / 100) . " &euro;";
	}
	
	public function getPreisInCent() {
		return $this->data['buchPreis'];
	}
	
	public function getPreisInEuro() {
	    return str_replace(".",",",$this->data['buchPreis'] / 100);
	}
	
	public function getFach() {
		return $this->data['buchFach'];
	}
	
	public function getKlasse() {
		return $this->data['buchKlasse'];
	}
	
	public function getVerlag() {
		return $this->data['buchVerlag'];
	}
	
	private function setAttr($name, $val) {
	    DB::getDB()->query("UPDATE schulbuch_buecher SET $name='" . DB::getDB()->escapeString($val) . "' WHERE buchID='" . $this->getID() . "'");
	}
	
	public function setName($name) {
	    $this->setAttr('buchTitel', $name);
	}
	
	public function setISBN($isbn) {
	    $this->setAttr('buchISBN', $isbn);
	}
	
	public function setPreis($preis) {
	    $this->setAttr('buchPreis', intval(str_replace(",",".",$preis)*100));
	}
	
	public function setFach($fach) {
	    $this->setAttr('buchFach', $fach);
	}
	
	public function setKlasse($klasse) {
	    $this->setAttr('buchKlasse', $klasse);
	}
	
	public function setVerlag($verlag) {
	    $this->setAttr('buchVerlag', $verlag);
	}
	public function delete() {
		
		$exemplare = $this->getExemplare();
		
		for($i = 0; $i < sizeof($exemplare); $i++) $exemplare[$i]->delete();
		
		DB::getDB()->query("DELETE FROM schulbuch_buecher WHERE buchID='" . $this->getID() . "'");
		
	}
	
	
	
	/**
	 * 
	 * @return Exemplar[]
	 */
	public function getExemplare() {
		return Exemplar::getBySchulbuch($this);
	}
	
	/**
	 * 
	 * @param string $isBankbuch
	 * @return number
	 */
	public function getBestand($isBankbuch=false) {
		return Exemplar::getBestandBySchulbuch($this,$isBankbuch);
	}
	
	public function getLentBestand($isBankbuch) {
		return Exemplar::getLentBestand($this, $isBankbuch);
	}
	
	
	public static function getAll() {
		$allSQL = DB::getDB()->query("SELECT * FROM schulbuch_buecher ORDER BY buchKlasse ASC, buchFach ASC");
		
		$alleBuecher = [];
		
		while($b = DB::getDB()->fetch_array($allSQL)) {
			$alleBuecher[] = new Schulbuch($b);
		}
		
		return $alleBuecher;
	}
	
	/**
	 * 
	 * @param int $id
	 * @return Schulbuch|NULL
	 */
	public static function getByID($id) {
		$buch = DB::getDB()->query_first("SELECT * FROM schulbuch_buecher WHERE buchID='" . $id . "'");
		if($buch['buchID'] > 0) return new Schulbuch($buch);
		return null;
	}
	
	/**
	 * 
	 * @param String[] $barcodes
	 * @return String[] Fehlermeldungen
	 */
	public function addExemplare($barcodes, $zustand, $anschaffungsjahr, $isBankbuch, $lagerort) {
		
		$currentActiveBarcodes = [];
		
		$errors = [];
		
		$allBarcodesSQL = DB::getDB()->query("SELECT exemplarBarcode FROM schulbuch_exemplare");
		while($b = DB::getDB()->fetch_array($allBarcodesSQL)) $currentActiveBarcodes[] = $b['exemplarBarcode'];
		
		for($i = 0; $i < sizeof($barcodes); $i++) {
			if($barcodes[$i] != '') {
				if(!in_array($barcodes[$i], $currentActiveBarcodes)) {
					$currentActiveBarcodes[] = $barcodes[$i];	// Neuen Barcode auch erfassen
					Exemplar::addExemplar($this, $barcodes[$i], $zustand, $anschaffungsjahr, $isBankbuch, $lagerort);
				}
				else $errors[] = "Der Barcode '" . $barcodes[$i] . "' wird bereits früher einmal verwendet oder ist doppelt erfasst worden. Das Exemplar wurde nicht erfasst.";
			}
		}
		
		return $errors;
	}
	
	//public static function createNew($_POST['titel'],$_POST['verlag'],$_POST['isbn'],$_POST['preis'],$_POST['fach'],$_POST['jahrgangsstufe']) {
	public static function createNew($titel, $verlag, $isbn, $preis, $fach,$jahrgangsstufe) {
		DB::getDB()->query("INSERT INTO schulbuch_buecher (
			buchTitel,
			buchVerlag,
			buchISBN,
			buchPreis,
			buchFach,
			buchKlasse,
			buchErfasserUserID
		) values (
			'" . DB::getDB()->escapeString($titel) . "',
			'" . DB::getDB()->escapeString($verlag) . "',
			'" . DB::getDB()->escapeString($isbn) . "',
			'" . intval(str_replace(",",".",$preis)*100) . "',
			'" . DB::getDB()->escapeString($fach) . "',
			'" . DB::getDB()->escapeString($jahrgangsstufe) . "',
			'" . DB::getUserID() . "'

		)");
	
	}
	
}

