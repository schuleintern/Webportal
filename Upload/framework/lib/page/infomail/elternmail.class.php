<?php



class elternmail extends AbstractPage {
	
	public function __construct() {
		parent::__construct ( array (
			"Infomail" 
		) );
		
		$this->checkLogin();
		
	}
	
	public function execute() {
		// Meine ESIS Nachrichten anzeigen
		
		$mails = DB::getDB()->query("SELECT *, elternmail_mails.mailID FROM elternmail_mails JOIN elternmail ON elternmail.mailID=elternmail_mails.elternmailID LEFT JOIN schueler ON schuelerAsvID=elternmailSchuelerAsvID WHERE mailUserID = '" . DB::getSession()->getUserID() . "' ORDER BY mailTime DESC");
		
		$mailData = array();
		while($mail = DB::getDB()->fetch_array($mails)) {
			$mailData[] = $mail;
		}
		
		$sentMailData = "";
		
		for($i = 0; $i < sizeof($mailData); $i++) {
			
			$mailData[$i]['mailText'] = str_replace("{MAILID}",$mailData[$i]['mailID'], $mailData[$i]['mailText']);
			$mailData[$i]['mailText'] = str_replace("{MAILSECRET}",$mailData[$i]['mailConfirmLinkSecret'], $mailData[$i]['mailText']);
			
			if($mailData[$i]['schuelerName'] != "")	$mailData[$i]['mailText'] = str_replace("{BETRIFFT}",$mailData[$i]['schuelerName'] . ", " . $mailData[$i]['schuelerRufname'] . "(Klasse " . $mailData[$i]['schuelerKlasse'] . ")", $mailData[$i]['mailText']);
				
			else $mailData[$i]['mailText'] = str_replace("{BETRIFFT}", "Selbst", $mailData[$i]['mailText']);
				
			
			$mailData[$i]['mailText'] = preg_replace('/((https:\/\/)\S+)/', '<a href="$1">$1</a>', $mailData[$i]['mailText']);
			
			eval("\$sentMailData .= \"" . DB::getTPL()->get("elternmail/eltern/index_bit") . "\";");
		}
		
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/eltern/index") . "\");");
		exit(0);
	}
	
	public static function hasUnconfirmedMails() {
		$mails = DB::getDB()->query("SELECT * FROM elternmail_mails JOIN elternmail ON elternmail.mailID=elternmail_mails.elternmailID WHERE mailUserID='" . DB::getSession()->getUserID() . "' AND mailConfirmed=0 AND mailRequireConfirmation=1 ORDER BY mailTime DESC");
		return DB::getDB()->num_rows($mails) > 0;	
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
		return 'Elternmail - Elternansicht';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
}

?>