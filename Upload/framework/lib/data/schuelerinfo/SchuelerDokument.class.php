<?php


class SchuelerDokument {
	/**
	 * 
	 * @var unknown
	 */
	private $data;
	
	/**
	 * 
	 * @var schueler
	 */
	private $schueler;
	
	/**
	 * 
	 * @var FileUpload
	 */
	private $upload;
	
	private function __construct($data) {
		$this->data = $data;
		$this->schueler = new schueler($data);
		$this->upload = new FileUpload($data);
	}
	
	public function getSchueler() {
		return $this->schueler;
	}
	
	public function getUpload() {
		return $this->upload;
	}
	
	public function getName() {
		return $this->data['dokumentName'];
	}
	
	
	public function getKommentar() {
		return $this->data['dokumentKommentar'];
	}
	
	public function delete() {
		$this->upload->delete();
		DB::getDB()->query("DELETE FROM schuelerinfo_dokumente WHERE dokumentID='" . $this->getID() . "'");
	}
	
	public function getID() {
		return $this->data['dokumentID'];
	}
	
	public function isNotiz() {
	    return $this->upload != null && $this->upload->getMimeType() == 'text/plain';
	}
	
	public function getNotizContent() {
	    return $this->upload->getTextFileContent();
	}
		
	/**
	 * 
	 * @param schueler $schueler
	 * @return SchuelerDokument[]
	 */
	public static function getAllForSchueler($schueler) {
		$data = DB::getDB()->query("SELECT * FROM schuelerinfo_dokumente JOIN schueler ON dokumentSchuelerAsvID=schuelerAsvID LEFT JOIN uploads ON dokumentUploadID=uploadID WHERE schuelerAsvID='" . $schueler->getAsvID() . "' ORDER BY uploadTime DESC");
		
		$objects = [];
		
		while($d = DB::getDB()->fetch_array($data)) {
			$objects[] = new SchuelerDokument($d);
		}
		
		return $objects;
	}
	
	public static function addKommentar($schueler, $notizname, $kommentar) {
	    $upload = FileUpload::uploadTextFileContents($notizname, $kommentar);
	    if($upload['result']) {
	        $uploadObject = $upload['uploadobject'];
	        DB::getDB()->query("INSERT INTO schuelerinfo_dokumente (dokumentSchuelerAsvID, dokumentName, dokumentKommentar, dokumentUploadID) values('" . $schueler->getAsvID() . "','" . DB::getDB()->escapeString($notizname) . "','','" . $uploadObject->getID() . "')");
	        return true;
	    }
	    
	    return false;
	}
	
	public static function uploadFile($schueler, $fileName, $kommentar, $fieldName) {
		$upload = FileUpload::uploadOfficePdfOrPicture($fieldName, $fileName);
		if($upload['result']) {
			$uploadObject = $upload['uploadobject'];
			DB::getDB()->query("INSERT INTO schuelerinfo_dokumente (dokumentSchuelerAsvID, dokumentName, dokumentKommentar, dokumentUploadID) values('" . $schueler->getAsvID() . "','" . DB::getDB()->escapeString($fileName) . "','" . DB::getDB()->escapeString($kommentar) . "','" . $uploadObject->getID() . "')");
			return true;
		}
		
		return false;
	}
	
	/**
	 * 
	 * @param schueler $schueler
	 * @param String $fileName
	 * @param TCPDF $tcPDF
	 * @return boolean
	 */
	public static function uploadFileFromTCPDF($schueler, $fileName, $tcPDF) {
		$upload = FileUpload::uploadFromTCPdf($fileName, $tcPDF);
		
		if($upload['result']) {
			$uploadObject = $upload['uploadobject'];
			DB::getDB()->query("INSERT INTO schuelerinfo_dokumente (dokumentSchuelerAsvID, dokumentName, dokumentKommentar, dokumentUploadID) values('" . $schueler->getAsvID() . "','" . DB::getDB()->escapeString($fileName) . "','','" . $uploadObject->getID() . "')");
			return true;
		}
		
		return false;
	}
	
	public static function getByID($id) {
		$data = DB::getDB()->query_first("SELECT * FROM schuelerinfo_dokumente JOIN schueler ON dokumentSchuelerAsvID=schuelerAsvID JOIN uploads ON dokumentUploadID=uploadID WHERE dokumentID='" . DB::getDB()->escapeString($id) . "' ORDER BY uploadTime DESC");
		if($data['uploadID'] > 0) {
			return new SchuelerDokument($data);
		}
		
		return null;
	}
	
	
}