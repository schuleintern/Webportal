<?php 

class dokumenteGruppe {
	
	private static $all = [];
	
	private $data;

	private $files = [];
	
	private static $mime_types = array(
            
            'application/zip',

            'application/pdf',

            'application/msword',
            'application/rtf',
            'application/vnd.ms-excel',
            'application/vnd.ms-powerpoint',

            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet'
        );
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getID() {
		return $this->data['gruppenID'];
	}
	
	public function getName() {
		return $this->data['gruppenName'];
	}
	
	public function getSectionID() {
		return $this->data['kategorieID'];
	}
	
	public function delete() {
		$files = $this->getFiles();
		
		for($i = 0; $i < sizeof($files); $i++) {
			$files[$i]->delete();
		}
		
		DB::getDB()->query("DELETE FROM dokumente_gruppen WHERE gruppenID='" . $this->getID() . "'");
	}
	
	/**
	 * Läd eine Datei in den Bereich hoch.
	 */
	public function uploadFile() {
		if ($_FILES['newFile']['error'] !== UPLOAD_ERR_OK) {
			new errorPage("Es gab einen Fehler beim Upload: " . $_FILES['file']['error']);
			exit();
		
		}
						
		$mime = null;
			
	    $ext = strtolower(array_pop(explode('.',$_FILES['newFile']['name'])));
	    
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $_FILES['newFile']['tmp_name']);
            finfo_close($finfo);
            if(!in_array($mimetype, self::$mime_types)) $mime = $mimetype;
        }
			
		if($mime != null) {
		
			DB::getDB()->query("INSERT INTO dokumente_dateien
					(gruppenID,
					dateiName,
					dateiAvailibleDate,
					dateiUploadTime,
					dateiKommentar,
					dateiMimeType,
					dateiExtension)
					values(
					'" . $this->getID() . "',
					'" . DB::getDB()->escapeString($_POST['fileName']) . "',
					'" . DateFunctions::getMySQLDateFromNaturalDate($_POST['fileDate']) . "',
					UNIX_TIMESTAMP(),
					'" . DB::getDB()->escapeString($_POST['fileKommentar']) . "',	
					'" . $mime . "',
					'" . $ext . "')");
			
			$newID = DB::getDB()->insert_id();
			
			@move_uploaded_file($_FILES["newFile"]["tmp_name"], "../data/dokumente/" . $newID . ".dat");
			
			return true;
		}
		else {
			new errorPage("Die Datei konnte leider nicht hochgeladen werden.");
		}
	}
	
	public function updateName($name) {
		DB::getDB()->query("UPDATE dokumente_gruppen SET gruppenName='" . DB::getDB()->escapeString($name) . "' WHERE gruppenID='" . $this->getID() . "'");
	}
	
	/**
	 * @return dokumenteDokument[] Dokumente
	 */
	public function getFiles($withAll=true) {
		if(sizeof($this->files) == 0) {
			$allData = DB::getDB()->query("SELECT * FROM dokumente_dateien WHERE gruppenID='" . $this->getID() . "'" . ((!$withAll) ? (" AND dateiAvailibleDate <= CURDATE() ") : ("")) . " ORDER BY dateiAvailibleDate DESC");
			while($d = DB::getDB()->fetch_array($allData)) $this->files[] = new dokumenteDokument($d);
		}
		
		return $this->files;
	}
	
	/**
	 * @return dokumenteGruppe[] alle Gruppen
	 */
	public static function getAll() {
		if(sizeof(self::$all) == 0) {
			$allData = DB::getDB()->query("SELECT * FROM dokumente_gruppen ORDER BY gruppenName ASC");
			while($d = DB::getDB()->fetch_array($allData)) self::$all[] = new dokumenteGruppe($d);
		}
		
		return self::$all;
	}
	
	/**
	 * 
	 * @param int $id
	 * @return dokumenteGruppe|NULL
	 */
	public static function getByID($id) {
		$all = self::getAll();
		
		for($i = 0; $i < sizeof($all); $i++) {
			if($all[$i]->getID() == $id) {
				return $all[$i];
			}
		}
		
		return null;
	}
	
	
}

