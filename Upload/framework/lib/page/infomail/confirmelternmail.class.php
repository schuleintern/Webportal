<?php



class confirmelternmail extends AbstractPage {
	
	public function __construct() {
		
		$this->needLicense = false;
		
		parent::__construct ( array (
			"Infomail","Empfang bestätigen"
		) );
			
	}
	public function execute() {
		$mailID = $_GET['mailID'];
		$code = $_GET['a'];
		
		$mail = DB::getDB ()->query_first ( "SELECT * FROM elternmail_mails WHERE mailID='" . DB::getDB ()->escapeString ( $mailID ) . "'" );
		
		if ($mail ['mailID'] > 0) {
			$elternMail = DB::getDB ()->query_first ( "SELECT * FROM elternmail WHERE mailID='" . $mail ['elternmailID'] . "'" );
			if ($elternMail ['mailRequireConfirmation'] > 0 && $elternMail['hasFormElements'] == 0) {
				if ($mail ['mailID'] == $mailID && $mail ['mailConfirmLinkSecret'] == $code) {
					DB::getDB ()->query ( "UPDATE elternmail_mails SET mailConfirmed=UNIX_TIMESTAMP(), mailConfirmedChannel='LINK' WHERE mailID='" . DB::getDB ()->escapeString ( $mailID ) . "'" );
					
					if(DB::isLoggedIn()) eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/confirm/confirmed_logged_in") . "\");");
					else eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/confirm/confirmed") . "\");");
					
					exit(0);
				}
			}
			else if($elternMail['hasFormElements'] > 0) {
				// Zeige Formularfelder an
				
				if($_GET['save'] > 0) {
					$formElements = DB::getDB()->query("SELECT * FROM elternmail_formelements  WHERE formelementMailID='" . $elternMail['mailID'] . "'");
					$formFieldsHTML = "";
					while($f = DB::getDB()->fetch_array($formElements)) {
						DB::getDB()->query("DELETE FROM elternmail_formelements_data WHERE formelementID='" . $f['formelementID'] . "' AND mailID='" . $mail['mailID'] . "'");
						DB::getDB()->query("INSERT INTO elternmail_formelements_data (formelementID, mailID, formelementData) values('" . $f['formelementID'] . "','" . $mail['mailID'] . "','" . DB::getDB()->escapeString($_POST['field_' . $f['formelementID'] ]) . "')");
					}
					
					DB::getDB ()->query ( "UPDATE elternmail_mails SET mailConfirmed=UNIX_TIMESTAMP(), mailConfirmedChannel='LINK' WHERE mailID='" . DB::getDB ()->escapeString ( $mailID ) . "'" );
						
					$message = "<div class=\"callout callout-success\">Ihre Eingaben wurden erfolgreich gespeichert!</div>";
				} else $message = "";
				
				$formElementsData = DB::getDB()->query("SELECT * FROM elternmail_formelements_data WHERE formelementID IN (SELECT formelementID FROM elternmail_formelements  WHERE formelementMailID='" . $elternMail['mailID'] . "') AND mailID='" . $mailID . "'");
				$data = array();
				while($d = DB::getDB()->fetch_array($formElementsData)) {
					$data[$d['formelementID']] = $d;
				}
								
				$formElements = DB::getDB()->query("SELECT * FROM elternmail_formelements  WHERE formelementMailID='" . $elternMail['mailID'] . "'");
				$formFieldsHTML = "";
				while($f = DB::getDB()->fetch_array($formElements)) {
					
					$formFieldsHTML .= "<h4>" . $f['formelementTitle'] . "</h4>";
					
					if($f['formElementType'] == "TEXT") {
						$formFieldsHTML .= "<textarea name=\"field_" . $f['formelementID'] . "\" class=\"form-control\" rows=\"5\" placeholder=\"Geben Sie hier bitte Ihre Antwort ein!\">";
						if(isset($data[$f['formelementID']])) $formFieldsHTML .= @htmlspecialchars($data[$f['formelementID']]['formelementData']);
						$formFieldsHTML .= "</textarea>";
					}
					
					
					if($f['formElementType'] == "NUMBER") {
						$formFieldsHTML .= "<input type=\"number\" name=\"field_" . $f['formelementID'] . "\"class=\"form-control\" rows=\"5\" placeholder=\"Geben Sie hier Ihre Anzahl ein!\" value=\"";
						if(isset($data[$f['formelementID']])) $formFieldsHTML .= @htmlspecialchars($data[$f['formelementID']]['formelementData']);
						$formFieldsHTML .= "\">";
					}
					
					if($f['formElementType'] == "BOOLEAN") {
						$formFieldsHTML .= "<select name=\"field_" . $f['formelementID'] . "\"class=\"form-control\">";
						if(isset($data[$f['formelementID']]) && $data[$f['formelementID']] == 1) $ja = true;
						else $ja = false;
						
						$formFieldsHTML .= "<option value=\"-1\">&nbsp;</option>";
						$formFieldsHTML .= "<option value=\"1\"" . (($ja) ? ("selected=\"selected\"") : ("")) . ">Ja</option>";
						$formFieldsHTML .= "<option value=\"0\"" . ((!$ja) ? ("selected=\"selected\"") : ("")) . ">Nein</option>";
						
						$formFieldsHTML .= "</select>";
					}
					
					$formFieldsHTML .= "<hr noshade>";
				}
				if(DB::isLoggedIn()) eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/confirm/form_fields_logged_in") . "\");");
				else eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/confirm/form_fields") . "\");");
				exit(0);
			}
		}
		
		
		die("Falscher Zugriff!");
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
		return '';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];
	
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
}

?>