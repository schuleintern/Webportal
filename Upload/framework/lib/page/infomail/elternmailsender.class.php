<?php


class elternmailsender extends AbstractPage {
	
	private $isMaiLAdmin = false;
	
	public function __construct() {
		
		$this->needLicense = false;
		
		parent::__construct ( array (
			"Infomail" 
		) );
		
		$this->checkLogin();
		
		if(!DB::getSession()->isAdmin()) {
			$this->checkAccessWithGroup("Webportal_Elternmail");
		}
			
	}
	
	public function execute() {
		switch($_GET['mode']) {
			default:
				new errorPage("Malformed Request!");
				exit(0);
			break;
			
			case 'sent':
				$this->sentMails();
				exit(0);
			break;
			
			case "send":
				$this->sendMail();
				exit(0);
			break;
			
			case "printFehlListen":
				$this->fehlListen();
			break;
			
			case "resendMails":
				$this->resendMails();
			break;
			
			case "getAnswers":
				$this->getAnswers();
			break;
		}
	}
	
	private function getAnswers() {
		$mail = DB::getDB()->query_first("SELECT * FROM elternmail WHERE mailID='" . DB::getDB()->escapeString($_GET['mailID']) . "'");
		if($mail['mailID'] > 0) {
			$answers = new AnswerXLSXCreator($mail['mailID']);
			$answers->createXLSX();
			exit();
		}
		else {
			new errorPage("Unbekannte Mail!");
		}
	}
	
	private function resendMails() {
		$mail = DB::getDB()->query_first("SELECT * FROM elternmail WHERE mailID='" . DB::getDB()->escapeString($_GET['mailID']) . "'");
		
		if($mail['mailID'] > 0) {
			DB::getDB()->query("UPDATE elternmail_mails SET mailSent=0 WHERE elternmailID='" . $mail['mailID'] . "' AND mailConfirmed=0");
		}
		
		header("Location: index.php?page=elternmailsender&mode=sent");
		exit(0);
	}
	
	private function fehlListen() {
		$klassenData = DB::getDB()->query("SELECT DISTINCT schuelerKlasse, mailTitle FROM elternmail_mails JOIN schueler ON elternmailSchuelerAsvID=schuelerAsvID JOIN elternmail ON elternmail.mailID=elternmail_mails.elternmailID WHERE elternmailID='" . DB::getDB()->escapeString($_GET['mailID']) . "' ORDER BY length(schuelerKlasse) ASC, schuelerKlasse ASC");
	
		$klassen = array();
		while($klasse = DB::getDB()->fetch_array($klassenData)) {
			$klassen[] = $klasse['schuelerKlasse'];
			$mailTitle = $klasse['mailTitle'];
		}
			
		$mpdf=new mPDF('utf-8', 'A4-P');
			
		$mpdf->ignore_invalid_utf8 = true;
	
			
		eval("\$header = \"" . DB::getTPL()->get("elternmail/sent/printFehlliste/index") . "\";");
			
		$header = ($header);
			
		$mpdf->WriteHTML($header,1);
	
	
	
		for($i = 0; $i < sizeof($klassen); $i++) {
				
			$angemeldetData = "";
	
			$schueler = DB::getDB()->query("SELECT schuelerName, schuelerRufname FROM schueler WHERE schuelerKlasse='" . $klassen[$i] . "' AND schuelerAsvID NOT IN (SELECT elternSchuelerAsvID FROM  eltern_email) ORDER BY schuelerName ASC, schuelerRufname ASC");
	
			while ($angemeldet = DB::getDB()->fetch_array($schueler)) {
				$angemeldetData .= "<tr><td>" . $angemeldet['schuelerName'] . ", " . $angemeldet['schuelerRufname'] . "</td></tr>";
			}
				
			if($angemeldetData != "") {
				$klassen[$i] = str_replace(" ", "_", $klassen[$i]);
		
				eval("\$klasse = \"" . DB::getTPL()->get("elternmail/sent/printFehlliste/bit") . "\";");
		
				$klasse = ($klasse);
		
				$mpdf->WriteHTML($klasse,2);
		
				if($i != (sizeof($klassen)-1)) $mpdf->AddPage();
			}
	
		}
	
		$mpdf->Output("Fehlliste_$mailTitle.pdf",'D');
		exit(0);
			
	}
	
	private function sentMails() {
		// Übersicht über alle Mails
		
		$mails = DB::getDB()->query("SELECT * FROM elternmail ORDER BY mailTime DESC LIMIT 100");
		
		$sentMailData = "";
		
		while($mail = DB::getDB()->fetch_array($mails)) {
			$datum = functions::makeDateFromTimestamp($mail['mailTime']);
			
			
			////////////////////////////////////////////////////////
			
			$klassenArrayEltern = array();
			$klassenEltern = explode(",",$mail['sendElternKlassen']);
			
			for($i = 0; $i < sizeof($klassenEltern); $i++) {
				$klassenArrayEltern[] = "<a href=\"index.php?page=elternmailinfo&gradeEltern=" . $klassenEltern[$i] . "&mailID=" . $mail['mailID'] . "\">" .  $klassenEltern[$i] . "</a>";
			}
			$klassenStringEltern = implode(", ",$klassenArrayEltern);
			
			
			////////////////////////////////////////////////////////
			
			$klassenArraySchueler = array();
			$klassenSchueler = explode(",",$mail['sendSchuelerKlassen']);
				
			for($i = 0; $i < sizeof($klassenSchueler); $i++) {
				$klassenArraySchueler[] = "<a href=\"index.php?page=elternmailinfo&gradeSchueler=" . $klassenSchueler[$i] . "&mailID=" . $mail['mailID'] . "\">" .  $klassenSchueler[$i] . "</a>";
			}
			$klassenStringSchueler = implode(", ",$klassenArraySchueler);
			
			
			////////////////////////////////////////////////////////
			
			
			$gruppenString = array();
			$otherGroups = explode(",",$mail['sendOther']);
			
			for($i = 0; $i < sizeof($otherGroups); $i++) {
				$gruppenString[] = "<a href=\"index.php?page=elternmailinfo&group=" . urlencode($otherGroups[$i]) . "&mailID=" . $mail['mailID'] . "\">" .  $otherGroups[$i] . "</a>";
			}
			$gruppenString = implode(", ",$gruppenString);
			
			
			////////////////////////////////////////////////////////
					
			
			$mailInfo = DB::getDB()->query_first("SELECT
					(SELECT COUNT(mailID) FROM elternmail_mails WHERE elternmailID='" . $mail['mailID'] . "' AND mailSent=0) AS NOT_SENT,
					(SELECT COUNT(mailID) FROM elternmail_mails WHERE elternmailID='" . $mail['mailID'] . "' AND mailSent>0) AS SENT,
					(SELECT COUNT(mailID) FROM elternmail_mails WHERE elternmailID='" . $mail['mailID'] . "' AND mailConfirmed>0) AS CONFIRMED
					FROM elternmail_mails");
			
			if($mailInfo['SENT'] > 0 || $mailInfo['NOT_SENT'] > 0) {
				$percentSent = $mailInfo['SENT'] / ($mailInfo['SENT'] + $mailInfo['NOT_SENT']) * 100;
				$percentSent = round($percentSent);
					
				$percentConfirmed = $mailInfo['CONFIRMED'] / ($mailInfo['SENT'] + $mailInfo['NOT_SENT']) * 100;
				$percentConfirmed = round($percentConfirmed);
				
				if($mailInfo['SENT'] < ($mailInfo['SENT'] + $mailInfo['NOT_SENT'])) {
					$mailStatus = "<font color=\"red\">Im Versand<br />" . $mailInfo['SENT'] . " von " . ($mailInfo['SENT'] + $mailInfo['NOT_SENT']) . " Mails versendet.</font>";
				}
				else {
					$mailStatus = "<font color=\"green\">Vollständig versendet.</font>";
				}
			}
			else {
				$percentConfirmed = 100;
				$percentSent = 100;
				
				$mailStatus = "Diese Aussendung enthält keine E-Mails";
			}
			

					

			
			
			
			eval("\$sentMailData .= \"" . DB::getTPL()->get("elternmail/sent/index_bit") . "\";");
		}
		
		if($sentMailData == "") $sentMailData = "<tr><td colspan=\"6\"><i>Keine vorhanden</i></td></tr>";
		
		eval("echo(\"" . DB::getTPL()->get("elternmail/sent/index") . "\");");
	}
	
	private function sendMail() {
		if($_GET['doSend'] > 0) {
			
			$grades = grade::getAllGrades();
			
			$sendGradesEltern = array();
			$sendGradesSchueler = array();
			
			for($i = 0; $i < sizeof($grades); $i++) {
				if($_POST['eltern' . $grades[$i]] > 0) {
					$sendGradesEltern[] = $grades[$i];
				}
				
				if($_POST['schueler' . $grades[$i]] > 0) {
					$sendGradesSchueler[] = $grades[$i];
				}
			}

			if($_POST['mailSubject'] == "") {
				$this->showSendForm("Der Betreff darf nicht leer sein!");
				exit();
			}
			
			if($_POST['mailText'] == "") {
				$this->showSendForm("Der Text darf nicht leer sein!");
				exit();
			}

			
			// Andere Gruppen
			
			$sendGruppen = [];
			$gruppen = DB::getDB()->query("SELECT DISTINCT groupName FROM elternmail_groups");
			while($g = DB::getDB()->fetch_array($gruppen)) {
				if($_POST['group_' . str_replace("_"," ",$g['groupName'])] > 0) $sendGruppen[] = $g['groupName'];
			}
			
			// Attchments prüfen
			
			$saveAttachments = array();
			
			for($i = 1; $i <= 10; $i++) {
				if(isset($_FILES['mailattachment' . $i]['tmp_name']) && $_FILES['mailattachment' . $i]['tmp_name'] != "") {
					$extension = end(explode(".",$_FILES['mailattachment' . $i]['name']));
					
					if(strtolower($extension) != "pdf") {
						$this->showSendForm("Der Anhang Nummer " . $i . " ist keine PDF Datei!");
						exit(0);
					}
					
					if($_FILES['mailattachment' . $i]['error'] != 0) {
						$this->showSendForm("Der Anhang Nummer " . $i . " ist keine PDF Datei! (Es ist ein Fehler beim Hochladen aufgetreten!)");
						exit(0);
					}
						
					
					$saveAttachments[] = $i;
				}
			}
			
			
			$needConfirmation = (($_POST['mailNeedConfirmation'] == 1) ? true : false);
			$mailAccessKlassenleitung = (($_POST['mailAccessKlassenleitung'] == 1) ? true : false);
			
			DB::getDB()->query("INSERT INTO elternmail 
				(
					mailTime,
					mailTitle,
					mailSubject,
					mailText,
					mailSender,
					mailRequireConfirmation,
					mailAccessKlassenleitung,
					sendElternKlassen,
					sendSchuelerKlassen,
					sendLehrer,
					sendOther
					)
				values(
					UNIX_TIMESTAMP(),
					'" . addslashes($_POST['mailSubject']) . "',
					'" . addslashes($_POST['mailSubject']) . "',
					'',
					'" . DB::getSession()->getUserID() . "',
					'" . (($needConfirmation) ? 1 : 0) . "',
					'" . (($mailAccessKlassenleitung) ? 1 : 0) . "',
					'" . implode(",",$sendGradesEltern) . "',
					'" . implode(",",$sendGradesSchueler) . "',
					'" . (($_POST['sendTeacher']) > 0 ? 1 : 0) . "',
					'" . implode(",",$sendGruppen) . "'
				)					
			"); // Der Text wird unten aktualisiert
			
			$newMailID = DB::getDB()->insert_id();
			
			$hasAttachments = false;
			
			$attachments = "";
			
			for($i = 0; $i < sizeof($saveAttachments) ; $i++) {
				
				$viewCode = addslashes(substr(md5(rand() . $_FILES['mailattachment' . $saveAttachments[$i]]['name']),0,10));
				
				DB::getDB()->query("INSERT INTO elternmail_attachments (attachmentMailID, attachmentFilename, attachmentAccessKey) values(
						
						'" . $newMailID . "',
						'" .addslashes($_FILES['mailattachment' . $saveAttachments[$i]]['name']) . "',
						'" . $viewCode . "'						
						)");
				$uploadID = DB::getDB()->insert_id();
				
				
				
				if(!move_uploaded_file($_FILES['mailattachment' . $saveAttachments[$i]]['tmp_name'], "elternmailattachment/" . $uploadID . ".pdf")) {
					DB::getDB()->query("DELETE FROM elternmail_attachments WHERE attachmentID='" . $uploadID . "'");
					// Falls Upload fehl schläft in letzter Instanz, dann Anhang entfernen.
					// Leider geht die Mail dann trotzdem raus.
				}
				else {
					$hasAttachments = true;
					
					$attachments .= "Anhang " . ($i+1) . ": " . DB::getGlobalSettings()->urlToIndexPHP . "?page=getelternmailattachment&attachmentID=" . $uploadID ."&a=" . $viewCode;
					
					if($i != sizeof($saveAttachments)-1) $attachments .= "\r\n";
				}
			}
			
			
			// Fragen
			
			$hasFormFields = false;
			
			$questionsText = "";
			
			for($i = 1; $i <= 10; $i++) {
				if(isset($_POST['question_' . $i . "_question"]) && $_POST['question_' . $i . "_question"] != "") {
					DB::getDB()->query("INSERT INTO elternmail_formelements (formelementMailID, formelementTitle, formElementType) values(
							'" . $newMailID . "',
							'" . addslashes($_POST['question_' . $i . "_question"]) . "',
							'" . DB::getDB()->escapeString($_POST['question_' . $i . "_type"]) . "'
					)");
					
					$questionsText .= "- " . $_POST['question_' . $i . "_question"] . "\r\n";
					$hasFormFields = true;
				}
			}


			// Mails erstellen		
			$mailtext = "";
			
			$trenner = "----------------------------------------------";
			
			$mailtext = "Betreff: " . $_POST['mailSubject'] . "\r\n" . $trenner . "\r\n";
			
			if($hasAttachments) {
				$mailtext .= "Anhänge:\r\n" . $attachments . "\r\n$trenner\r\n";
			}
			
			if($needConfirmation && !$hasFormFields) {
				$mailtext .= "Bitte bestätigen die den Empfang dieser Nachricht durch Antworten auf die Nachricht ohne Veränderung der Betreffzeile. Alternativ können Sie auch auf den folgenden Link klicken. Bitte beachten Sie, dass Sie uns dabei keine Nachricht übermitteln können. Wollen Sie uns eine Nachricht zukommen lassen, so schreiben Sie bitte eine E-Mail an " . DB::getSettings()->getValue("elternmail-kontaktmail") . "\r\n";
				$mailtext .= "Empfang bestätigen: " . DB::getGlobalSettings()->urlToIndexPHP . "?page=confirmelternmail&mailID={MAILID}&a={MAILSECRET}";
				$mailtext .= "\r\n" . $trenner . "\r\n";
			}
			
			if($hasFormFields) {
				$mailtext .= "Bitte bestätigen die den Empfang dieser Nachricht durch Klicken auf folgenden Link:\r\n";
				$mailtext .= "Empfang bestätigen: " . DB::getGlobalSettings()->urlToIndexPHP . "?page=confirmelternmail&mailID={MAILID}&a={MAILSECRET}";
				$mailtext .= "\r\nBitte beachten Sie, dass diese Mail nicht durch Antworten bestätigen können, da eine Antwort von Ihnen zu folgenden Fragen benötigt wird:\r\n";
				$mailtext .= $questionsText;
				$mailtext .= $trenner . "\r\n";
			}
			
			$mailtext .= "Diese Nachricht betrifft: {BETRIFFT}\r\n$trenner\r\n";
			
			$mailtext .= $_POST['mailText'];
			
			// $mailtext .= $trenner . "\r\n" . DB::getSettings()->getValue("elternmail-footer");
			
			DB::getDB()->query("UPDATE elternmail SET mailText='" . addslashes($mailtext) . "' WHERE mailID='" . $newMailID . "'");
			
			{
				// Eltern
				
				$sqlInserts = array();
			
				
				$schueler = DB::getDB()->query("SELECT * FROM eltern_email JOIN schueler ON eltern_email.elternSchuelerAsvID=schueler.schuelerAsvID WHERE schueler.schuelerKlasse IN ('" . implode("','", $sendGradesEltern) . "') ORDER BY elternEMail ASC");
				
				while($e = DB::getDB()->fetch_array($schueler)) {
					$secret = substr(md5(rand()),0,5);
					$sqlInserts[] = "($newMailID,(SELECT userID FROM users WHERE userName LIKE '" . addslashes($e['elternEMail']) . "' LIMIT 1),'" . $e['elternSchuelerAsvID'] . "','" . $secret . "')";
				}
				
				if(sizeof($sqlInserts) > 0) DB::getDB()->query("INSERT INTO elternmail_mails
						(
							elternmailID,
							mailUserID,
							elternmailSchuelerAsvID,
							mailConfirmLinkSecret
						) values
						" . implode(",",$sqlInserts) . "
						
						");
				
			}
			
			if($hasFormFields) {
				DB::getDB()->query("UPDATE elternmail SET hasFormElements=1 WHERE mailID='" . $newMailID . "'");
			}
			
			{
				// Schüler
				
				$sqlInserts = array();
				
				
				$users = DB::getDB()->query("SELECT * FROM schueler JOIN users ON schuelerUserID=userID WHERE schuelerKlasse IN ('" . implode("','", $sendGradesSchueler) . "')");
				
				while($user = DB::getDB()->fetch_array($users)) {
					$secret = substr(md5(rand()),0,5);
					$sqlInserts[] = "($newMailID,'" . $user['userID'] . "','','" . $secret . "')";
				}
				
				if(sizeof($sqlInserts) > 0) DB::getDB()->query("INSERT INTO elternmail_mails
						(
							elternmailID,
							mailUserID,
							elternmailSchuelerAsvID,
							mailConfirmLinkSecret
						) values
						" . implode(",",$sqlInserts) . "
				
						");
			}
			
			{
				
				$sqlInserts = array();
				
				// Lehrer
				if($_POST['sendTeacher'] > 0) {
					$users = DB::getDB()->query("SELECT * FROM lehrer JOIN users ON lehrerUserID=userID");
					
					while($user = DB::getDB()->fetch_array($users)) {
						$secret = substr(md5(rand()),0,5);
						$sqlInserts[] = "($newMailID,'" . $user['userID'] . "','','" . $secret . "')";
					}
					
					if(sizeof($sqlInserts) > 0) DB::getDB()->query("INSERT INTO elternmail_mails
						(
							elternmailID,
							mailUserID,
							elternmailSchuelerAsvID,
							mailConfirmLinkSecret
						) values
						" . implode(",",$sqlInserts) . "
					
						");
				}
			}
			
			{
			
				if(sizeof($sendGruppen) > 0) {
					
					$sqlInserts = array();
					$users = DB::getDB()->query("SELECT * FROM users WHERE userID IN (SELECT userID FROM elternmail_groups WHERE groupName IN ('" . implode("','",$sendGruppen) . "'))");
							
					while($user = DB::getDB()->fetch_array($users)) {
						$secret = substr(md5(rand()),0,5);
						$sqlInserts[] = "($newMailID,'" . $user['userID'] . "','','" . $secret . "')";
					}
						
					if(sizeof($sqlInserts) > 0) DB::getDB()->query("INSERT INTO elternmail_mails
							(
								elternmailID,
								mailUserID,
								elternmailSchuelerAsvID,
								mailConfirmLinkSecret
							) values
							" . implode(",",$sqlInserts) . "
				
							");
				}
			}
			
			
			eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternmail/send/sent") . "\");");
			exit(0);
		}
		else {
			$this->showSendForm();
		}
	}
	
	private function showSendForm($error="") {
		$optionsGrades = "";
		
		$allGrades = grade::getAllGrades();
		
		$klassenData = array();
		
		$minStufe = 1000;
		$maxStufe = 0;
		
		for($i = 0; $i < sizeof($allGrades); $i++) {
			if(substr($allGrades[$i],0,2) >= 10) {
				$stufe = substr($allGrades[$i],0,2);
			}
			else {
				$stufe = substr($allGrades[$i],0,1);
			}
			
			if(is_numeric($stufe)) {
				
			if(!is_array($klassenData[$stufe]) && $allGrades[$i] != "") {
				$klassenData[$stufe] = array();
				if($stufe < $minStufe) $minStufe = $stufe;
				if($stufe > $maxStufe) $maxStufe = $stufe;
			}
			
			if($allGrades[$i] != "") $klassenData[$stufe][] = $allGrades[$i];
			}
		}
		
// 		Debugger::debugObject($klassenData,1);
		
		// Wie viele Klassenstufen wirklich vorhanden?
		
		$realCountGrades = 0;
		for($i = $minStufe; $i <= $maxStufe; $i++) {
			if(is_array($klassenData[$i])) $realCountGrades++;
		}
		
		$perRow = floor($realCountGrades);
		
		$gradeSelectHTML = $this->getGradeSelectHTML($klassenData, "eltern", $minStufe, $maxStufe);
		$gradeSelectHTMLPupil = $this->getGradeSelectHTML($klassenData, "schueler", $minStufe, $maxStufe);
		
		$groupHTML = "";
		$groups = DB::getDB()->query("SELECT DISTINCT groupName FROM elternmail_groups ORDER BY groupName ASC");
		while($g = DB::getDB()->fetch_array($groups)) {
			$groupHTML .= "<input type=\"checkbox\" name=\"group_" . $g['groupName'] . "\" value=\"1\"> " . $g['groupName'] . "<br />";
		}
		
		if($_POST['mailText'] != "") $message = $_POST['mailText'];
		else $message = DB::getSettings()->getValue("elternmail-defaultMessage");
		
		if($error != "") {
			$errorHTML = "<div class=\"callout callout-danger\">" . $error . "<br /><u>Achtung:</u> Sie müssen die Anhänge leider erneut auswählen!</div>";
		}
		
		$questionHTML = "";
		
		for($i = 1; $i <= 10; $i++) {
			$questionHTML .= "<tr><td>" . $i . "</td><td><select name=\"question_" . $i . "_type\" class=\"form-control\"><option value=\"TEXT\">Freier Text</option><option value=\"NUMBER\">Ganze Zahl</option><option value=\"BOOLEAN\">Ja / Nein Frage</option></select></td><td><input type=\"text\" name=\"question_" . $i . "_question\" class=\"form-control\" placeholder=\"Frei lassen, wenn nicht verwendet.\"></td></tr>";
		}
		
			
		eval("echo(\"" . DB::getTPL()->get("elternmail/send/index") . "\");");
	}
	
	private static function getGradeSelectHTML($klassenData, $prefix, $minStufe, $maxStufe) {
		$gradeSelectHTML = "<table class=\"table table-bordered\">";
		
		$selectAll = "";
		$unselectAll = "";
		
		$gradeSelectHTML .= "<tr>";
		for($i = $minStufe; $i <= $maxStufe; $i++) {
			$gradeSelectHTML .= "<td>" . $i . ". Klasse<br />";
				
			$gradeSelectHTML .= "<a href=\"#\" onclick=\"javascript:";
				
			for($k = 0; $k < sizeof($klassenData[$i]); $k++) {
				$gradeSelectHTML .= "document.getElementById('" . $prefix.$klassenData[$i][$k] . "').checked = true;";
				$selectAll .= "document.getElementById('" . $prefix.$klassenData[$i][$k] . "').checked = true;";
			}
				
			$gradeSelectHTML .= "\"><i class=\"fa fa-check\"></i> Alle Auswählen</a><br /><a href=\"#\" onclick=\"javascript:";
				
			for($k = 0; $k < sizeof($klassenData[$i]); $k++) {
				$gradeSelectHTML .= "document.getElementById('" . $prefix.$klassenData[$i][$k] . "').checked = false;";
				$unselectAll .= "document.getElementById('" . $prefix.$klassenData[$i][$k] . "').checked = false;";
			}
				
			$gradeSelectHTML .= "\"><i class=\"fa fa-ban\"></i> Nicht auswählen</a></td>";
		}
		$gradeSelectHTML .= "</tr><tr>";
		
		for($i = $minStufe; $i <= $maxStufe; $i++) {
			$gradeSelectHTML .= "<td>";
				
			for($k = 0; $k < sizeof($klassenData[$i]); $k++) {
				$gradeSelectHTML .= "<label><input type=\"checkbox\"" . (($_POST[$prefix.$klassenData[$i][$k]] == "1") ? " checked=\"checked\"" : "") . " name=\"" . $prefix.$klassenData[$i][$k] . "\" value=\"1\" id=\"" . $prefix.$klassenData[$i][$k] . "\"> Klasse " . $klassenData[$i][$k] . "</label><br />";
			}
				
			$gradeSelectHTML .= "</td>";
		}
		$gradeSelectHTML .= "</tr>";
		$gradeSelectHTML .= "<tr><td colspan=\"" . sizeof($klassenData) . "\"><a href=\"#\" onclick=\"javascript:" . $selectAll . "\"><i class=\"fa fa-check\"></i> Alle Klassen auswählen</a> | <a href=\"#\" onclick=\"javascript:" . $unselectAll . "\"><i class=\"fa fa-ban\"></i> Keine Klasse auswählen</a></td></tr></table>";
		
		return $gradeSelectHTML;
	}
	
	public static function getNotifyItems() {
		return array();
	}
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getSiteDisplayName() {
		return "Versand";
	}
	
	public static function getSettingsDescription() {
		$settings =  array(
				array(
						"name" => "elternmail-defaultMessage",
						"typ" => "TEXT",
						"titel" => "Standardnachricht",
						"text" => "Standard Nachricht für den Versand neuer Elternmails. Der Text kann vor jedem Versenden geändert werden."
				),
				array(
						"name" => "elternmail-kontaktmail",
						"typ" => "ZEILE",
						"titel" => "E-Mailadresse für Verwaltungskontakt",
						"text" => ""
				),
				array(
						'name' => 'elternmail-footer',
						'typ' => 'ZEILE',
						'titel' => 'Footer Text der Mails',
						'text' => 'Footertext der Mails (z.B. Hinweise zum Austragen)'
				)
		);
		
		
		return $settings;
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array(
				array(
						'groupName' => 'Webportal_Elternmail',
						'beschreibung' => 'Zugriff auf das Elternmailsystem zum Versenden von Mails.'
				)
		);
	
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Elternmail';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-envelope';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-envelope';
	}
	
	public static function getAdminMenuGroup() {
		return 'Infomail';
	}
}

?>