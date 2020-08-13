<?php


class getelternmailattachment extends AbstractPage {
	
	public function __construct() {
		
		$this->needLicense = false;
		
		parent::__construct ( array (
			"ESIS" 
		) );
			
	}
	
	public function execute() {
		$attachment = DB::getDB()->query_first("SELECT * FROM elternmail_attachments WHERE attachmentID='" . intval($_GET['attachmentID']) . "'");
		
		if($_GET['a'] == $attachment['attachmentAccessKey']) {
			header('Content-Description: Dateidownload');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $attachment['attachmentFilename'] .'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize("elternmailattachment/" . $attachment['attachmentID'] . ".pdf"));
			readfile("elternmailattachment/" . $attachment['attachmentID'] . ".pdf");
			
			exit(0);
		}
		else {
			eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/attachment/attachmentNotFound") . "\");");
			PAGE::kill(true);
			//exit(0);
		}
		
		print_r($attachment);
		die();
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
		return 'Elternmail: Anhang downloaden';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
}

?>