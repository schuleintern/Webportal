<?php


class FileUpload {
		
	private static $mimeTypesMSOffice = array(
			'application/msword',
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'application/vnd.ms-word.document.macroEnabled.12',
			'application/vnd.ms-word.template.macroEnabled.12',
			'application/vnd.ms-excel',
			'application/vnd.ms-excel',
			'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'application/vnd.ms-excel.sheet.macroEnabled.12',
			'application/vnd.ms-excel.template.macroEnabled.12',
			'application/vnd.ms-excel.addin.macroEnabled.12',
			'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'application/vnd.ms-powerpoint',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'application/vnd.openxmlformats-officedocument.presentationml.template',
			'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'application/vnd.ms-powerpoint.addin.macroEnabled.12',
			'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'application/vnd.ms-powerpoint.template.macroEnabled.12',
			'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'application/vnd.ms-access',
			'application/pdf',
			'application/zip'
	);
	
	private static $mimesPicture = [
			'image/png',
			'image/tiff',
			'image/jpeg',
			'image/jpg',
			'image/gif'
	];
	
	private $data = [];
	
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getFileName() {
		return $this->data['uploadFileName'];
	}
	
	public function getExtension() {
		return $this->data['uploadFileExtension'];
	}
	
	public function getMimeType() {
		return $this->data['uploadFileMimeType'];
	}
	
	public function getUploadTime() {
		return $this->data['uploadTime'];
	}
	
	public function isImage() {
	    return in_array($this->data['uploadFileMimeType'], self::$mimesPicture);
	}
	
	public function isWord() {
	    return $this->getExtension() == 'doc' || $this->getExtension() == 'docx';
	}
	
	public function isExcel() {
	    return $this->getExtension() == 'xls' || $this->getExtension() == 'xlsx';
	}
	
	public function isPowerpoint() {
	    return $this->getExtension() == 'ppt' || $this->getExtension() == 'ppts';
	}
	
	public function isPDF() {
	    return $this->getMimeType() == 'application/pdf';
	}
	
	public function getAccessCode() {
		return $this->data['fileAccessCode'];
	}
	
	public function reuploadJPEGImageFromBase64($base64) {
	    $ifp = fopen( $this->getFilePath(), 'wb' );
	    
	    $data = explode( ',', $base64 );
	    
	    fwrite( $ifp, base64_decode( $data[ 1 ] ) );
	    
	    fclose( $ifp );
	    
	    DB::getDB()->query("UPDATE uploads SET uploadFileMimeType='image/jpeg' WHERE uploadID='" . $this->getID() . "'");
	    DB::getDB()->query("UPDATE uploads SET uploadFileExtension='jpg' WHERE uploadID='" . $this->getID() . "'");
	}
	
	public function getTextFileContent() {
	    if($this->getMimeType() == 'text/plain') return file_get_contents($this->getFilePath());
	    else return 'no text Content - invalid mime type';
	}
	
	public function getURLToFile($forceDownload=false) {
		
		if($this->getAccessCode() == '') {
			$this->data['fileAccessCode'] = strtoupper(md5(rand()) . md5(rand()));
			DB::getDB()->query("UPDATE uploads SET fileAccessCode='" . $this->data['fileAccessCode'] . "' WHERE uploadID='" . $this->getID() . "'");
		}
		
		return DB::getGlobalSettings()->urlToIndexPHP . "?page=FileDownload&uploadID=" . $this->getID() . "&accessCode=" . $this->getAccessCode() . (($forceDownload) ? ("&fd=1") : (""));
	}
	
	/**
	 * Direkter Pfad zur Datei
	 * @return string
	 */
	public function getFilePath() {
	    return "../data/uploads/" . $this->getID() . ".dat";
	}
	
	/**
	 * 
	 * @return user|NULL
	 */
	public function getUploader() {
		return user::getUserByID($this->data['uploaderUserID']);
	}
	
	public function getID() {
		return $this->data['uploadID'];
	}
	
	public function delete() {
		@unlink('../data/uploads/' . $this->getID() . ".dat");
		DB::getDB()->query("DELETE FROM uploads WHERE uploadID='" . $this->getID() . "'");
	}
	
	public function getFileTypeIcon() {
		switch(strtolower($this->getExtension())) {
			case 'pdf':
				return 'fa fa-file-pdf-o';
				
			case 'doc':
			case 'docx':
			    return 'fa fa-file-word-o';
			    
			case 'xls':
			case 'xlsx':
			    return 'fa fa-file-excel-o';
			    
			case 'ppt':
			case 'pptx':
			    return 'fa fa-file-powerpoint-o';
			    
			
			default:
				return 'fa fa-file-o';
		}
	}
	
	public function getFileSize() {
		if(!file_exists("../data/uploads/" . $this->getID() . ".dat")) {
			return 'n/a';
		}
		
		return str_replace(".",",",round(filesize("../data/uploads/" . $this->getID() . ".dat") / 1024 / 1024,2)) . " MB";
	}
	
	
	public function sendFile() {
		if(!file_exists("../data/uploads/" . $this->getID() . ".dat")) {
			new errorPage("Upload existiert nicht!");
			exit(0);
		}
				
		header('Content-Description: Dateidownload');
		header('Content-Type: ' . $this->getMimeType());
		header('Content-Disposition: attachment; filename="'. $this->getFileName() . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize("../data/uploads/" . $this->getID() . ".dat"));

		ob_clean();
		flush();

        $fp = fopen("../data/uploads/" . $this->getID() . ".dat", 'rb');		// READ / BINARY

        fpassthru($fp);
		
		exit(0);
	}
	
	public function sendPreviewForPDFFirstPage($width) {
	    if(!file_exists("../data/uploads/" . $this->getID() . ".dat")) {
	        new errorPage("Upload existiert nicht!");
	    }
	    
	    if($this->getMimeType() != "application/pdf") new errorPage();
	    
	    $this->sendPDFPageAsImage(1);
	}
	
	public function sendPDFPageAsImage($page) {
	    if(!file_exists("../data/uploads/" . $this->getID() . ".dat")) {
	        new errorPage("Upload existiert nicht!");
	    }
	    
	    if($this->getMimeType() != "application/pdf") new errorPage();
	    
	    $showPage = 0;
	    
	    if($page <= $this->getPDFPageNumber()) {
	        $showPage = $page-1;
	    }
	    
	    $image = new Imagick("../data/uploads/" . $this->getID() . ".dat[" . $showPage ."]");
	    
	    $image->setImageFormat('jpg');
	    
	    header('Content-Type: image/jpeg');
	    echo $image;
        exit();	    
	}
	
	public function getPDFPageNumber() {
	    
	    if(!file_exists("../data/uploads/" . $this->getID() . ".dat")) {
	        new errorPage("Upload existiert nicht!");
	    }
	   	    
	    $image = new Imagick();
	    $image->pingImage("../data/uploads/" . $this->getID() . ".dat");
	    return $image->getNumberImages();
	}
	
	public function sendImageWidthMaxWidth($maxWidth) {
		
		if(!file_exists("../data/uploads/" . $this->getID() . ".dat")) {
			new errorPage("Upload existiert nicht!");
		}
		
		$oldSize = getImageSize ( "../data/uploads/" . $this->getID() . ".dat" );
		
		$scale = $maxWidth / $oldSize [0];
		
		$newWidth = round ( $oldSize [0] * $scale );
		$newHeight = round ( $oldSize [1] * $scale );
		
		$altesBild = ImageCreateFromJPEG ( "../data/uploads/" . $this->getID() . ".dat" );
		
		if($this->getExtension() == 'png') {
		    $altesBild = imagecreatefrompng( "../data/uploads/" . $this->getID() . ".dat" );
		}
		
		$neuesBild = imagecreatetruecolor ( $newWidth, $newHeight );
		
		ImageCopyResized ( $neuesBild, $altesBild, 0, 0, 0, 0, $newWidth, $newHeight, $oldSize [0], $oldSize [1] );
		
		header ( "Content-type: image/jpeg" );
		
		ImageJPEG ( $neuesBild );
		
		exit ( 0 ); // Script zur Sicherheit beenden
	}
	
	/**
	 * 
	 * @param int $id
	 * @return FileUpload|null
	 */
	public static function getByID($id) {
		$upload = DB::getDB()->query_first("SELECT * FROM uploads WHERE uploadID='" . DB::getDB()->escapeString($id) . "'");
		if($upload['uploadID'] > 0) {
			return new FileUpload($upload);
		}
		
		return null;
	}
	
	/**
	 * 
	 * @param String $fieldName
	 * @param String $fileName
	 * @return String[]
	 */
	public static function uploadPicture($fieldName, $fileName) {


		return self::uploadFileImpl($fieldName, self::$mimesPicture, $fileName);	
	}
	
	public static function uploadPDF($fieldName, $fileName) {
		$mimePDF = [
				'application/pdf'
		];
		
		return self::uploadFileImpl($fieldName, $mimePDF, $fileName);	
	}
	
	public static function uploadCSV($fieldName, $fileName) {
	    $mime = [
	        'text/csv',
	        'text/plain'
	    ];
	    
	    return self::uploadFileImpl($fieldName, $mime, $fileName);
	}
	public static function uploadPowerpoint($fieldName, $fileName) {
	    $mimePDF = [
	        'application/vnd.openxmlformats-officedocument.presentationml.presentation'
	    ];
	    
	    return self::uploadFileImpl($fieldName, $mimePDF, $fileName);
	}
	
	public static function uploadOfficeDocumentsAndPDF($fieldName, $fileName) {
		$mimes = self::$mimeTypesMSOffice;
		$mimes[] = 'application/pdf';
		
		for($i = 0; $i < sizeof(self::$mimesPicture); $i++) {
		    $mimes[] = self::$mimesPicture[$i];
		}
		
		return self::uploadFileImpl($fieldName, $mimes, $fileName);	
	}
	
	public static function uploadOfficeDocument($fieldName, $fileName) {
		return self::uploadFileImpl($fieldName, self::$mimeTypesMSOffice, $fileName);
	}
	
	public static function uploadOfficePdfOrPicture($fieldName, $fileName) {
		$mimes = self::$mimeTypesMSOffice;
		
		for($i = 0; $i < sizeof(self::$mimesPicture); $i++) {
		    $mimes[] = self::$mimesPicture[$i];
		}
		
		$mimes[] = 'application/pdf';
		
		
		return self::uploadFileImpl($fieldName, $mimes, $fileName);
	}
	
	public static function uploadPDFOrZip($fieldName, $fileName) {
	    $mimes = [];

	    $mimes[] = 'application/pdf';
	    $mimes[] = 'application/zip';	    
	    
	    return self::uploadFileImpl($fieldName, $mimes, $fileName);
	}
	
	
	
	
	/**
	 * 
	 * @param String $filename
	 * @param TCPDF $tcpdf
	 */
	public static function uploadFromTCPdf($filename, $tcpdf) {
		$mime = 'application/pdf';
		
		if(DB::isLoggedIn()) {
		
    		$user = DB::getSession()->getUser();
    		
    		if($user == null) {
    		    // SystemCall
    		    
    		    $userID = 0;
    		}
    		else $userID = $user->getUserID();
		
		}
		else $userID = 0;
		
		DB::getDB()->query("INSERT INTO uploads
				(
					uploadFileName,
					uploadFileExtension,
					uploadFileMimeType,
					uploadTime,
					uploaderUserID,
                    fileAccessCode
				) values(
					'" . DB::getDB()->escapeString($filename) . "',
					'pdf',
					'" . $mime . "',
					UNIX_TIMESTAMP(),
					" . $userID . ",
					'" . strtoupper(md5(rand()) . md5(rand())) . "'
				)
			");
		
		
		$newID = DB::getDB()->insert_id();

		$path = getcwd();

		$saveDir = "/../data/uploads/" . $newID . ".dat";

        $saveDir = $path . $saveDir;


        $tcpdf->Output($saveDir, 'F');
				
		$data = DB::getDB()->query_first("SELECT * FROM uploads WHERE uploadID='" . $newID. "'");
		
		return [
				'result' => true,
				'uploadobject' => new FileUpload($data),
				'mimeerror' => false,
				'text' => "Save from TCPDF OK"
		];
		
		
		
	}
	
	private static function uploadFileImpl($fieldName, $mimes, $fileName='') {
		if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
			return [
				'result' => false,
				'uploadobject' => null,
				'mimeerror' => false,
				'text' => "Es gab einen Fehler beim Upload: " . $_FILES['file']['error']
			];			
		}
		
		$mime = null;
		
		if($fileName == '') $fileName = $_FILES[$fieldName]['name'];
		
		$ext = strtolower(array_pop(explode('.',$_FILES[$fieldName]['name'])));
		
		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $_FILES[$fieldName]['tmp_name']);
			finfo_close($finfo);
			$mimetype = str_replace("; charset=binary", "", $mimetype);
			$mimetype = str_replace("; charset=utf-8", "", $mimetype);
						
			if(!in_array($mimetype, $mimes)) {
				$mime = null;
			}
			else $mime = $mimetype;
		}
		else new errorPage("MIME Type kann nicht bestimmt werden!");
		
		if($mime != null) {
			
			DB::getDB()->query("INSERT INTO uploads
				(
					uploadFileName,
					uploadFileExtension,
					uploadFileMimeType,
					uploadTime,
					uploaderUserID
				) values(
					'" . DB::getDB()->escapeString($fileName) . "',
					'" . $ext . "',
					'" . $mime . "',
					UNIX_TIMESTAMP(),
					" . DB::getSession()->getUser()->getUserID() . "				
				)
			");
			
			$newID = DB::getDB()->insert_id();

			move_uploaded_file($_FILES[$fieldName]["tmp_name"], "../data/uploads/" . $newID . ".dat");
			
			$data = DB::getDB()->query_first("SELECT * FROM uploads WHERE uploadID='" . $newID. "'");
			
			return [
					'result' => true,
					'uploadobject' => new FileUpload($data),
					'mimeerror' => false,
					'text' => "Upload OK"
			];
		}
		else {
			return [
					'result' => false,
					'uploadobject' => null,
					'mimeerror' => true,
					'text' => "wrong mime type"
			];
		}
	}
	
	public static function uploadTextFileContents($name, $content) {
	    $mime = 'text/plain';
	    
	    DB::getDB()->query("INSERT INTO uploads
				(
					uploadFileName,
					uploadFileExtension,
					uploadFileMimeType,
					uploadTime,
					uploaderUserID,
                    fileAccessCode
				) values(
					'" . DB::getDB()->escapeString($name) . "',
					'txt',
					'" . $mime . "',
					UNIX_TIMESTAMP(),
					" . DB::getSession()->getUser()->getUserID() . ",
					'" . strtoupper(md5(rand()) . md5(rand())) . "'
				)
			");
	    
	    
	    $newID = DB::getDB()->insert_id();
	    
	    $saveDir = "../data/uploads/" . $newID . ".dat";
	    
	    file_put_contents($saveDir, $content);
	    
	    $data = DB::getDB()->query_first("SELECT * FROM uploads WHERE uploadID='" . $newID. "'");
	    
	    return [
	        'result' => true,
	        'uploadobject' => new FileUpload($data),
	        'mimeerror' => false,
	        'text' => "Upload from Text - Content OK"
	    ];
	    
	}
	
	public static function generateUploadID($name, $extension, $isWord, $isPDF) {
	    
	    if($isWord) {
	        $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
	    }
	    
	    if($isPDF) {
	        $mime = 'application/pdf';
	    }
	    
	    DB::getDB()->query("INSERT INTO uploads
				(
					uploadFileName,
					uploadFileExtension,
					uploadFileMimeType,
					uploadTime,
					uploaderUserID,
                    fileAccessCode
				) values(
					'" . DB::getDB()->escapeString($name) . "',
					'" . DB::getDB()->escapeString($extension) . "',
					'" . $mime . "',
					UNIX_TIMESTAMP(),
					" . DB::getSession()->getUser()->getUserID() . ",
					'" . strtoupper(md5(rand()) . md5(rand())) . "'
				)
			");
	    
	    
	    $newID = DB::getDB()->insert_id();
	    
	    $saveDir = "../data/uploads/" . $newID . ".dat";
	    
	    // file_put_contents($saveDir, "");
	    
	    $data = DB::getDB()->query_first("SELECT * FROM uploads WHERE uploadID='" . $newID. "'");
	    
	    return [
	        'result' => true,
	        'uploadobject' => new FileUpload($data),
	        'mimeerror' => false,
	        'text' => "Upload from Text - Content OK"
	    ];
	    
	}
}

