<?php

class SchuelerBrief {
	
	private $data;
	
	private $schueler;
	
	/**
	 * 
	 * @param unknown $data JOIN aus Brief, Adresse und Schüler
	 */
	public function __construct($data) {
		$this->data = $data;
		$this->schueler = new schueler($data);
	}
	
	public function getID() {
		return $this->data['briefID'];
	}
	
	public function getAdresse() {
		return $this->data['briefAdresse'];
	}
	
	public function getAnrede() {
		return $this->data['briefAnrede'];
	}
	
	public function getLehrer() {
		return lehrer::getByASVId($this->data['briefLehrerAsvID']);
	}
	
	public function getBetreff() {
		return $this->data['briefBetreff'];
	}
	
	public function getDatum() {
		return DateFunctions::getNaturalDateFromMySQLDate($this->data['briefDatum']);
	}
	
	public function getText() {
		return $this->data['briefText'];
	}
	
	public function getUnterschrift() {
		return $this->data['briefUnterschrift'];
	}
	
	public function isErledigt() {
		return $this->data['briefVorgangErledigt'] > 0;
	}
	
	public function getErledigtDate() {
		return functions::makeDateFromTimestamp($this->data['briefVorgangErledigt']);
	}
	
	public function isBriefGedruckt() {
		return $this->data['briefGedruckt'] > 0;
	}
	
	public function getPrintDate() {
		return functions::makeDateFromTimestamp($this->data['briefGedruckt']);
	}
	
	public function getErledigtKommentar() {
		return $this->data['briefErledigtKommentar'];
	}
	
	public function getKommentar() {
		return $this->data['briefKommentar'];
	}
	
	/**
	 * 
	 * @return schueler
	 */
	public function getSchueler() {
		return $this->schueler;
	}
	
	public function setErledigt($kommentar) {
		DB::getDB()->query("UPDATE schueler_briefe SET briefVorgangErledigt=UNIX_TIMESTAMP(), briefErledigtKommentar='" . DB::getDB()->escapeString($kommentar) . "' WHERE briefID='" . $this->getID() . "'");
	}
	
	public function setAdresse($adresse) {
		$this->updateAttr("briefAdresse", $adresse);
	}
	
	public function setAnrede($anrede) {
		$this->updateAttr("briefAnrede", $anrede);
	}
	public function setBetreff($betreff) {
		$this->updateAttr("briefBetreff", $betreff);
	}
	public function setDatum($datum) {
		$this->updateAttr("briefDatum", DateFunctions::getMySQLDateFromNaturalDate($datum));
	}
	public function setText($text) {
		$this->updateAttr("briefText", $text);
	}
	public function setUnterschrift($unterschrift) {
		$this->updateAttr("briefUnterschrift", $unterschrift);
	}
	
	public function setKommentar($kommentar) {
		$this->updateAttr("briefKommentar", $kommentar);
	}
	
	private function updateAttr($field, $value) {
		if($this->data['briefSaveLonger'] == 0)
			DB::getDB()->query("UPDATE schueler_briefe SET $field='" . DB::getDB()->escapeString($value) . "' WHERE briefID='" . $this->getID() . "'");
		
		else
			DB::getDB()->query("UPDATE schueler_briefe SET briefSaveLonger=UNIX_TIMESTAMP(), $field='" . DB::getDB()->escapeString($value) . "' WHERE briefID='" . $this->getID() . "'");
	}
	
	public function delete() {
		DB::getDB()->query("DELETE FROM schueler_briefe WHERE briefID='" . $this->getID() . "'");
	}
	
	public function isSaveLonger() {
		return $this->data['briefSaveLonger'] == 0;
	}
	
	public function getLastChangeTime() {
		return $this->data['briefSaveLonger'];
	}
	
	public function setPrinted() {
		$this->updateAttr('briefGedruckt', time());
	}
	
	/**
	 * 
	 * @return TCPDF
	 */
	public function getLetterPDF() {
		$letter = new PrintLetterWithWindowA4($this->getBetreff());
		
		$letter->setBetreff($this->getBetreff());
		
		$html = nl2br($this->getAnrede()) . "<br /><br />" . $this->getText() . "<br /><br />" . $this->getUnterschrift();
		
		
		$letter->setDatum($this->getDatum());
		$letter->addLetter($this->getAdresse(), $html);
		
				
		$letter->send(true);
		
		return $letter;
		
	}
	
	public static function getByID($id) {
		$data = DB::getDB()->query_first("SELECT * FROM schueler_briefe JOIN schueler ON schueler.schuelerAsvID=schueler_briefe.schuelerAsvID WHERE briefID='" . intval($id) . "'");
	
		if($data['briefID'] > 0) return new SchuelerBrief($data);
		else return null;
	}
	
	/**
	 * 
	 * @param schueler $schueler
	 * @return SchuelerBrief[]
	 */
	public static function getBriefForSchueler($schueler) {
		$data = DB::getDB()->query("SELECT * FROM schueler_briefe JOIN schueler ON schueler.schuelerAsvID=schueler_briefe.schuelerAsvID WHERE schueler.schuelerAsvID='" . DB::getDB()->escapeString($schueler->getAsvID()) . "'");
		
		$briefe = [];
		
		while($d = DB::getDB()->fetch_array($data)) $briefe[] = new SchuelerBrief($d);
		
		return $briefe;
		
	}
	
	/**
	 * 
	 * @param schueler $schueler
	 * @param lehrer $teacher
	 * @return SchuelerBrief[]
	 */
	public static function getBriefForSchuelerAndTeacher($schueler, $teacher) {
		
		
		if($teacher == null) return self::getBriefForSchueler($schueler);
		
		$data = DB::getDB()->query("SELECT * FROM schueler_briefe JOIN schueler ON schueler.schuelerAsvID=schueler_briefe.schuelerAsvID WHERE schueler.schuelerAsvID='" . DB::getDB()->escapeString($schueler->getAsvID()) . "' AND briefLehrerAsvID='" . $teacher->getAsvID() . "'");
		
		$briefe = [];
		
		while($d = DB::getDB()->fetch_array($data)) $briefe[] = new SchuelerBrief($d);
		
		return $briefe;
		
	}
	
	/**
	 * 
	 * @param schueler $schueler
	 * @param lehrer $lehrer
	 * @param boolean $saveLonger
	 * @return NULL|SchuelerBrief
	 */
	public static function getNewLetter($schueler, $lehrer, $saveLonger) {
		if($saveLonger) $saveLonger = 0;
		else $saveLonger = time();
		
		if($lehrer != null) $lehrer = $lehrer->getAsvID();
		else $lehrer = '';
		
		DB::getDB()->query("INSERT INTO schueler_briefe (schuelerAsvID, briefLehrerAsvID,briefSaveLonger) values('" . $schueler->getAsvID() . "','" . $lehrer . "','" . $saveLonger . "')");
		$newID = DB::getDB()->insert_id();
		return self::getByID($newID);
	}
	

}

