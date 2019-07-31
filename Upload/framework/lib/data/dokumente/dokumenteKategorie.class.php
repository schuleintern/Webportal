<?php 

class dokumenteKategorie {
	
	private static $all = [];
	
	private $data;

	private $gruppen = [];
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getID() {
		return $this->data['kategorieID'];
	}
	
	public function getName() {
		return $this->data['kategorieName'];
	}
	
	public function getAccessSchueler() {
		return $this->data['kategorieAccessSchueler'] > 0;
	}
	
	public function getAccessLehrer() {
		return $this->data['kategorieAccessLehrer'] > 0;
	}
	
	public function getAccessEltern() {
		return $this->data['kategorieAccessEltern'] > 0;
	}
	
	public function setAccessLehrer($access) {
		DB::getDB()->query("UPDATE dokumente_kategorien SET kategorieAccessLehrer='" . ($access ? 1 : 0) . "' WHERE kategorieID='" . $this->getID() . "'");
	}
	
	public function setAccessSchueler($access) {
		DB::getDB()->query("UPDATE dokumente_kategorien SET kategorieAccessSchueler='" . ($access ? 1 : 0) . "' WHERE kategorieID='" . $this->getID() . "'");
	}
	
	public function setAccessEltern($access) {
		DB::getDB()->query("UPDATE dokumente_kategorien SET kategorieAccessEltern='" . ($access ? 1 : 0) . "' WHERE kategorieID='" . $this->getID() . "'");
	}
	
	public function rename($name) {
		DB::getDB()->query("UPDATE dokumente_kategorien SET kategorieName='" . $name . "' WHERE kategorieID='" . $this->getID() . "'");
	}
	
	public function delete() {
		$gruppen = $this->getGruppen();
		for($i = 0; $i < sizeof($gruppen); $i++) {
			$gruppen[$i]->delete();
		}
		
		DB::getDB()->query("DELETE FROM dokumente_kategorien WHERE kategorieID='" . $this->getID() . "'");
	}
	
	
	/**
	 * Überprüft, ob Zugriff mit dem aktuellen Benutzer besteht.
	 * @return boolean
	 */
	public function hasAccess() {
		if(DB::getSession()->isAdmin()) return true;
		
		if(DB::getSession()->isMember(dokumente::getAdminGroup())) return true;
		
		if(DB::getSession()->isMember('Webportal_Dokumente_All_Accesss')) return true;
		
		$isTeacher = DB::getSession()->isTeacher();
		$isEltern = DB::getSession()->isEltern();
		$isSchueler = DB::getSession()->isPupil();
				
		if($this->getAccessEltern() && $isEltern) return true;
		if($this->getAccessLehrer() && $isTeacher) return true;
		if($this->getAccessSchueler() && $isSchueler) return true;
		
		return false;
	}
	
	/**
	 * @return dokumenteGruppe[] Gruppen
	 */
	public function getGruppen() {
		if(sizeof($this->gruppen) == 0) {
			$allData = DB::getDB()->query("SELECT * FROM dokumente_gruppen WHERE kategorieID='" . $this->getID() . "'");
			while($d = DB::getDB()->fetch_array($allData)) $this->gruppen[] = new dokumenteGruppe($d);
		}
		
		return $this->gruppen;
	}
	
	/**
	 * @return dokumenteKategorie[] alle
	 */
	public static function getAll() {
		if(sizeof(self::$all) == 0) {
			$allData = DB::getDB()->query("SELECT * FROM dokumente_kategorien ORDER BY kategorieName ASC");
			while($d = DB::getDB()->fetch_array($allData)) self::$all[] = new dokumenteKategorie($d);
		}
		
		return self::$all;
	}
	
	/**
	 * Ermittelt die Kategorien, zu denen der aktuelle Nutzer Zugriff hat.
	 * @return dokumenteKategorie[]
	 */
	public static function getAllWithMyAccess() {
		$all = self::getAll();
		
		if(DB::getSession()->isAdmin()) return $all;
		
		if(DB::getSession()->isMember(dokumente::getAdminGroup())) return $all;
		
		if(DB::getSession()->isMember('Webportal_Dokumente_All_Accesss')) return $all;
		
		$isTeacher = DB::getSession()->isTeacher();
		$isEltern = DB::getSession()->isEltern();
		$isSchueler = DB::getSession()->isPupil();
	
		
		$my = [];
		
		for($i = 0; $i < sizeof($all); $i++) {
			if($all[$i]->getAccessEltern() && $isEltern) $my[] = $all[$i];
			if($all[$i]->getAccessLehrer() && $isTeacher) $my[] = $all[$i];
			if($all[$i]->getAccessSchueler() && $isSchueler) $my[] = $all[$i];
		}
		
		return $my;
	}
	
	public static function getByID($id) {
		$allData = DB::getDB()->query_first("SELECT * FROM dokumente_kategorien WHERE kategorieID='" . $id . "'");
		if($allData['kategorieID'] > 0) {
			return new dokumenteKategorie($allData);
		}
		return null;
	}
	
	
}

