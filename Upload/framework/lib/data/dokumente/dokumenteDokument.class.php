<?php 

class dokumenteDokument {
	
	private static $all = [];
	
	private $data;

	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getID() {
		return $this->data['dateiID'];
	}
	
	public function getName() {
		return $this->data['dateiName'];
	}
	
	public function getKommentar() {
		return $this->data['dateiKommentar'];
	}
	
	public function getDate() {
		return $this->data['dateiAvailibleDate'];
	}
	
	public function getDownloads() {
		return $this->data['dateiDownloads'];
	}
	
	public function getBereich() {
		return dokumenteGruppe::getByID($this->data['gruppenID']);
	}
	
	public function delete() {
		@unlink("../data/dokumente/" . $this->getID() . ".dat");
		DB::getDB()->query("DELETE FROM dokumente_dateien WHERE dateiID='" . $this->getID() . "'");
	}
	
	public function changeGroup($groupID) {
		DB::getDB()->query("UPDATE dokumente_dateien SET gruppenID='" . intval($groupID) . "' WHERE dateiID='" . $this->getID() . "'");
	}
	
	public function sendFile() {

		$filename = "../data/dokumente/" . $this->getID() . ".dat";
		$filesize = filesize($filename);

		if (!file_exists($filename) || $filesize <= 0) {
			// TODO: error msg at user interface ?
			return false;
		}
		
		DB::getDB()->query("UPDATE dokumente_dateien SET dateiDownloads=dateiDownloads+1 WHERE dateiID='" . $this->getID() . "'");
		
		header('Content-Description: Dateidownload');
		header('Content-Type: ' . $this->data['dateiMimeType']);
		header('Content-Disposition: attachment; filename="'. $this->getName() . "." . $this->data['dateiExtension'] . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $filesize);

		readfile($filename);

		exit(0);
	}
	
	public function getSizeInMB() {
		$size = @filesize("../data/dokumente/" . $this->getID() . ".dat");
		
		if($size > 0) {
			return str_replace(".",",",round(($size / 1024) /1024,2));
		}		
		else return "n/a";
	}
	
	
	/**
	 * @return dokumenteDokument[] alle Dokumente
	 */
	public static function getAll() {
		if(sizeof(self::$all) == 0) {
			$allData = DB::getDB()->query("SELECT * FROM dokumente_dateien ORDER BY dateiName ASC");
			while($d = DB::getDB()->fetch_array($allData)) self::$all[] = new dokumenteDokument($d);
		}
		
		return self::$all;
	}
	
	/**
	 * 
	 * @param int $id
	 * @return dokumenteDokument|NULL
	 */
	public static function getByID($id) {
		$all = self::getAll();
		
		for($i = 0; $i < sizeof($all); $i++) {
			if($all[$i]->getID() == $id) return $all[$i];
		}
		
		return null;
	}
	
	
}

