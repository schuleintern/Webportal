<?php


class administrationbadmails extends AbstractPage {

	private $info;
	
	const ADMINGROUP_BADMAILS = 'Webportal_BadMails';
	
	public function __construct() {
		$this->needLicense = false;
		
		parent::__construct(array("Administration", "Falsche E-Mailadressen"));
		
		new errorPage();
		
	}

	public function execute() {}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminGroup() {
		return self::ADMINGROUP_BADMAILS;
	}
	
	public static function displayAdministration($selfURL) {
		if($_GET['action'] == "confirm") {
			DB::getDB()->query("UPDATE bad_mail SET badMailDone=UNIX_TIMESTAMP() WHERE badMailDone=0");
		}
		
		if($_GET['showAll'] != 1) {
			$where = "WHERE 
					badMailDone=0 ";
		}
		else $where = "";
		
		$mails = DB::getDB()->query("SELECT 
				DISTINCT badMail,
                badMailID,
				schuelerName, 
				schuelerRufname, 
				schuelerKlasse,
				badMailDone
				
				FROM 
					bad_mail 
						LEFT JOIN eltern_email 
							ON badMail LIKE elternEMail 
						LEFT JOIN schueler 
							ON schuelerAsvID=elternSchuelerAsvID 
				$where
				
				ORDER BY 
					length(schuelerKlasse) ASC, 
					schuelerKlasse ASC,
					schuelerName ASC, 
					schuelerRufname ASC
		");
		
		$anzahl = 0;
		while($mail = DB::getDB()->fetch_array($mails)) {
			$anzahl++;

			if($_REQUEST['checkBadMailID'] == $mail['badMailID']) {
			    DB::getDB()->query("UPDATE bad_mail SET badMailDone=UNIX_TIMESTAMP() WHERE badMail LIKE '" . $mail['badMail'] . "'");
			    $mail['badMailDone'] = 1;
            }
			eval("\$mailHTML .= \"" . DB::getTPL()->get("administration/unknownmailsender/mail_bit") . "\";");
				
		}
		
		if($_GET['action'] == "pdf") {
			eval("\$printContent = \"" . DB::getTPL()->get("administration/unknownmailsender/print") . "\";");
			
			$printContent = ($printContent);

			$print = new PrintNormalPageA4WithHeader("Falsche E-Mailadresssen");
			$print->setHTMLContent($printContent);
			$print->send();
			exit(0);
		}
		else {	
			eval("\$html = \"" . DB::getTPL()->get("administration/unknownmailsender/index") . "\";");
			return $html;
		}
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
		return 'Falsche E-Mailadressen';
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
	
	public static function getAdminMenuGroup() {
		return 'E-Mailverwaltung';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-envelope';
	}
	
	public static function need2Factor() {
	    return true;
	}
	

}


?>