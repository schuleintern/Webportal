<?php 


/**
 * Sendet die Elternmailnachrichten
 * @author Christian
 *
 */

class ElternMailReceiver extends AbstractCron {
	
	private $isDebug = true;
	private $sendPerRound = 20;
	
	private $receivedMails = 0;
	
	public function __construct() {
		
	}
	
	public function execute() {
		
		if(DB::getGlobalSettings()->schulnummer != "9400") {
		
			$mb = imap_open("{" . DB::getGlobalSettings()->smtpSettings['host'] . ":143/imap}INBOX",DB::getGlobalSettings()->smtpSettings['username'], DB::getGlobalSettings()->smtpSettings['password'] );
			
			$messageCount = imap_num_msg($mb);
			
			if($messageCount > 200) $messageCount = 200;
			
			for( $MID = 1; $MID <= $messageCount; $MID++ ) {
				$EmailHeaders = imap_headerinfo( $mb, $MID );
							
				$betreff = mb_decode_mimeheader($EmailHeaders->subject);
				
				$this->receivedMails++;
							
				if(substr_count($betreff,"[") == 1) {
					if(substr_count($betreff,"]") == 1) {
						if(substr_count($betreff,"{") == 1 && substr_count($betreff,"}") == 1) {
							$mailID = substr($betreff,strpos($betreff, "[")+1,strpos($betreff, "]") - strpos($betreff, "[")-1);
							$code = substr($betreff,strpos($betreff, "{")+1,strpos($betreff, "}") - strpos($betreff, "{")-1);
							
							$mail = DB::getDB()->query_first("SELECT * FROM messages_messages WHERE messageID='" . DB::getDB()->escapeString($mailID) . "'");			
							
							if($mail['messageID'] > 0) {
								if($mail['messageNeedConfirmation'] > 0) {
									if($mail['messageID'] == $mailID && $mail['messageConfirmSecret'] == $code) {
										DB::getDB()->query("UPDATE messages_messages SET messageIsConfirmed=UNIX_TIMESTAMP() WHERE messageID='" . DB::getDB()->escapeString($mailID) . "'");
										imap_delete($mb,$EmailHeaders->Msgno);
									}
									else imap_delete($mb,$EmailHeaders->Msgno);
								}
								else {
									imap_delete($mb,$EmailHeaders->Msgno);
								}
							} else imap_delete($mb,$EmailHeaders->Msgno);
						} else imap_delete($mb,$EmailHeaders->Msgno);
					}
					else {
						$this->handleUnknownMail($mb, $MID);
						imap_delete($mb,$EmailHeaders->Msgno);
					}
				}
				else {
					$this->handleUnknownMail($mb, $MID);
					imap_delete($mb,$EmailHeaders->Msgno);
				}
			}
			
			imap_expunge($mb);	// Nachrichten löschen
			imap_close($mb);	// Verbindung schließen
		}
	}
		
		private function handleUnknownMail($mb, $mailID) {
			$EmailHeaders = imap_headerinfo( $mb, $mailID );
				
			$betreff = mb_decode_mimeheader($EmailHeaders->subject); 
			$text =  imap_body($mb,$mailID);
			$from =  mb_decode_mimeheader($EmailHeaders->fromaddress);
			
			$isBad = false;
			$data = explode ( "\n", $text );
			
			if($betreff == "Undelivered Mail Returned to Sender" || strpos("Undeliverable",$betreff) > 0) {
				for($i = 0; $i < sizeof ( $data ); $i ++) {
					if (substr ( $data [$i], 0, 15 ) == "Final-Recipient") {
						$data2 = explode ( ";", str_replace ( "\r", "", $data [$i] ) );
						$mail = strtolower ( trim ( $data2 [1] ) );
						DB::getDB ()->query ( "INSERT INTO bad_mail (badMail) values('" . DB::getDB ()->escapeString ( $mail ) . "')" );
						$isBad = true;
						break;
					}
				}
			}
			else {
				$text = DB::getDB()->escapeString($text);
				$betreff = DB::getDB()->escapeString($betreff);
				$from = DB::getDB()->escapeString($from);
				
				DB::getDB()->query("INSERT INTO unknown_mails (mailSubject, mailText, mailSender) values('" . $betreff . "','" . $text . "','" . $from . "')");
			}
			
		}
	
	
	public function getName() {
		return "E-Mail Rückmeldungen empfangen";
	}
	
	public function getDescription() {
		return "Verarbeitet die Rückmeldungen der E-Mails. (Lesebestätigungen)";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
		return ['success' => true, 'resultText' => $this->receivedMails . " empfangen."];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 300;		// Alle 5 Minuten
	}
}



?>