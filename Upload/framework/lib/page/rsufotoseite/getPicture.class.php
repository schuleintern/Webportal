<?php
class getPicture extends AbstractPage {
	private $sections = array ();
	public function __construct() {
		parent::__construct ( array (
				"" 
		) );
		
		if (! in_array ( "Webportal_Fotoseite", DB::getSession ()->getGroupNames () )) {
			// Nur eigenes Bild darf abgerufen werden.
			// Nur Lehrer
			if (DB::getSession ()->isTeacher ()) {
				$kuerzel = strtolower ( DB::getSession()->getData ( "userName" ) );
				$kuerzel = str_replace ( "ue", "ü", $kuerzel);
				$kuerzel = str_replace ( "oe", "ö", $kuerzel);
				$kuerzel = str_replace ( "ss", "ß", $kuerzel);
				
				$person = DB::getDB ()->query_first ( "SELECT * FROM rsu_persons WHERE personKuerzel LIKE '" . $kuerzel . "'" );
				if ($person ['personID'] > 0)
					$_GET ['personID'] = $person ['personID'];
				else
					$_GET ['personID'] = 0;
			} else {
				header ( "Location: index.php" );
				exit ( 0 );
			}
		} else {
			if (! isset ( $_GET ['personID'] )) {
				$kuerzel = strtolower ( DB::getSession()->getData ( "userName" ) );
				$kuerzel = str_replace ( "ue", "ü", $kuerzel);
				$kuerzel = str_replace ( "oe", "ö", $kuerzel);
				$kuerzel = str_replace ( "ss", "ß", $kuerzel);
				
				$person = DB::getDB ()->query_first ( "SELECT * FROM rsu_persons WHERE personKuerzel LIKE '" . $kuerzel . "'" );
				
				if ($person ['personID'] > 0)
					$_GET ['personID'] = $person ['personID'];
				else
					$_GET ['personID'] = 0;
			}
		}
	}
	public function execute() {
		$maxWidth = 300;
		
		
		$id = $_GET ['personID'];
		
		if ($id > 0) {
			
			$imageFile = "rsufotoseite/$id.jpg";
		} else {
			$imageFile = "images/userimages/default.png";
		}
		
		$oldSize = getImageSize ( $imageFile );
		
		$scale = $maxWidth / $oldSize [0];
		
		$newWidth = round ( $oldSize [0] * $scale );
		$newHeight = round ( $oldSize [1] * $scale );
		
		$altesBild = ImageCreateFromJPEG ( $imageFile );
		$neuesBild = imagecreatetruecolor ( $newWidth, $newHeight );
		
		ImageCopyResized ( $neuesBild, $altesBild, 0, 0, 0, 0, $newWidth, $newHeight, $oldSize [0], $oldSize [1] );
		
		header ( "Content-type: image/jpeg" );
		
		ImageJPEG ( $neuesBild );
		
		exit ( 0 ); // Script zur Sicherheit beenden
	}
	
	public static function hasSettings() {
		return false;
	}
	
	/**
	 * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
	 * @return array(String, String)
	 * array(
	 * 	   array(
	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
	 *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
	 *      'titel' => "Titel der Beschreibung",
	 *      'text' => "Text der Beschreibung"
	 *     )
	 *     ,
	 *     .
	 *     .
	 *     .
	 *  )
	 */
	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Fotoseite: Bild auslesen';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function onlyForSchool() {
		return array(
				"0740"
		);
	}
}

?>