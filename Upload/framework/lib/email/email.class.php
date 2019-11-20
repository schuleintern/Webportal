<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Stellt Funktionen zum versenden von E-Mails zur verfügung.
 * @author Christian Spitschka
 * @package 
 */
class email {

	private $recipient;
	private $subject;
	private $text;
	private $replyTo;
	private $cc;
	private $lesebestaetigung;
	private $isHTML = false;
	
	/**
	 * Anhänge
	 * @var int[]
	 */
	private $attachments = [];
	
	/**
	 * 
	 * @var FileUpload
	 */
	private static $attachmentCache = [];

	public function __construct($recipient,$subject,$message) {


	    $this->recipient = $recipient;
		$this->subject = $subject;
		$this->text = $message;
		$this->lesebestaetigung = false;
		
		
		
	}
	
	public function setReplyTo($rt) {
		$this->replyTo = $rt;
	}
	
	public function isHTML() {
	    $this->isHTML = true;
	}
	

	public function setCC($cc) {
		$this->cc = $cc;
	}
	
	public function getCC() {
		return $this->cc;
	}
	
	/**
	 * 
	 * @param FileUpload $fileUpload
	 */
	public function addAttachment($fileUpload) {
	    $this->attachments[] = $fileUpload->getID();
	}

	/**
	 * Sendet die Mail (über den CRAWLER für Massenversand)
	 */
	public function send() {
	    
	    
		DB::getDB()->query("INSERT INTO mail_send (mailRecipient, mailSubject, mailText, mailCrawler, replyTo, mailCC, mailLesebestaetigung, mailIsHTML, mailAttachments) values('" . DB::getDB()->escapeString($this->recipient) . "','" . addslashes($this->subject) . "','" . DB::getDB()->escapeString($this->text) . "', 1, '" . $this->replyTo . "','" . $this->cc . "','" . ($this->lesebestaetigung ? 1 : 0) . "','" . ($this->isHTML ? 1 : 0) . "','" . implode(",", $this->attachments) . "')");
	}
	
	
	/**
	 * Sendset sofort eine Mail. (z.B. für Passwort Mails)
	 */
	public function sendInstantMail($overrideDebug=false) {
	    DB::getDB()->query("INSERT INTO mail_send (mailRecipient, mailSubject, mailText, mailCrawler, replyTo, mailCC, mailLesebestaetigung, mailIsHTML, mailAttachments) values('" . DB::getDB()->escapeString($this->recipient) . "','" . addslashes($this->subject) . "','" . DB::getDB()->escapeString($this->text) . "', 1, '" . $this->replyTo . "','" . $this->cc . "','" . ($this->lesebestaetigung ? 1 : 0) . "','" . ($this->isHTML ? 1 : 0) . "','" . implode(",", $this->attachments) . "')");
	    self::sendMailWithID(DB::getDB()->insert_id(), $overrideDebug);	// direkt versenden
	}
	
	/*
	 * Versendet 40 Mails aus dem Stapel der Mails
	 */
	public static function sendBatchMails() {
		
		$mails = DB::getDB()->query("SELECT mailID FROM mail_send WHERE mailSent=0 AND mailCrawler=1 LIMIT 40");
		
		$noError = true;
		
		$count = 0;
		
		while($m = DB::getDB()->fetch_array($mails)) {
			try {
				self::sendMailWithID($m['mailID']);
				$count++;
			}
			catch(Exception $e) {
                print_r($e->errorMessage());die();

                $noError = false;
			}
			catch(phpmailerException $e) {
				$noError = false;
			}
		}
		
		if($noError) return $count;
		else {
			return -1;
		}
	}

    	private static function sendMailWithID($id, $overrideDebug=false) {

				$m = DB::getDB()->query_first("SELECT * FROM mail_send WHERE mailID='" . $id . "'");


				if(DB::getSettings()->getValue("mail-server") == "") return;


				$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

				$mail->IsSMTP(); // telling the class to use SMTP

                if(DB::isDebug()) $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;                      // Enable verbose debug output


                $mail->Host       = DB::getSettings()->getValue("mail-server");
			  $mail->Port = DB::getSettings()->getValue("mail-server-port");


			  if(DB::getSettings()->getValue('mail-reply-to') != "") {
			      $mail->addReplyTo(DB::getSettings()->getValue('mail-reply-to'), DB::getSettings()->getValue('mail-reply-to-name'));
              }


			  if(DB::getSettings()->getBoolean("mail-server-auth")) {
			     $mail->Username   = DB::getSettings()->getValue("mail-server-username");;
			     $mail->Password   = DB::getSettings()->getValue("mail-server-password");
			     $mail->SMTPAuth   = DB::getSettings()->getBoolean("mail-server-auth");

                  if(DB::getSettings()->getBoolean("mail-server-auth-auto-tls")) {
                      $mail->SMTPAutoTLS = true;
                      $mail->SMTPSecure   = PHPMailer::ENCRYPTION_SMTPS;

                  }
                  else {
                      $mail->SMTPAutoTLS = false;
                      if( DB::getSettings()->getValue("mail-server-securetype") != "") {
                          if(DB::getSettings()->getValue("mail-server-securetype") ==  'starttls') {
                              $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                          }
                          elseif(DB::getSettings()->getValue("mail-server-securetype") ==  'smtps') {
                              $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                          }
                      }
                      else {
                          $mail->SMTPSecure = '';
                      }
                  }




              }
			  else {
			      $mail->SMTPAuth   = false;
			  }

			  if($m['mailIsHTML'] > 0) $mail->isHTML(true);
			  else $mail->isHTML(false);

			  $mail->CharSet = 'UTF-8';

			  $mail->AddAddress($m['mailRecipient']);


			  $mail->SetFrom(DB::getSettings()->getValue("mail-server-sender"), DB::getGlobalSettings()->schoolName);

			  if($m['mailCC'] != "") {
			  	$adresses = explode(";",$m['mailCC']);
			  	for($i = 0; $i < sizeof($adresses); $i++) {
			  		$mail->AddCC($adresses[$i]);
			  	}
			  }

			  $mail->Subject = $m['mailSubject'];

			  $mail->Body = $m['mailText'];

			  $attachments = explode(",",$m['mailAttachments']);

			  for($i = 0; $i < sizeof($attachments); $i++) {

			      if(self::$attachmentCache[$attachments[$i]] != null) {
			          $file = self::$attachmentCache[$attachments[$i]];
			      }
			      else {
			          $file = FileUpload::getByID($attachments[$i]);
			          self::$attachmentCache[$attachments[$i]] = $file;
			      }

			      if($file != null) {
			          $mail->addAttachment($file->getFilePath(), $file->getFileName() . "." . $file->getExtension());
			      }
			  }


			  if($m['replyTo'] != "") {
			  	$mail->AddReplyTo($m['replyTo']);
			  }

			  if($m['mailLesebestaetigung'] > 0) {
			  	$mail->addCustomHeader("Disposition-Notification-To: " . DB::getSettings()->getValue("mail-server-sender"));
			  	$mail->AddCustomHeader("X-Confirm-Reading-To: " . DB::getSettings()->getValue("mail-server-sender"));
			  	$mail->AddCustomHeader("Return-receipt-to: " . DB::getSettings()->getValue("mail-server-sender"));
			  }

			  if($mail->Send()) {
			  	DB::getDB()->query("UPDATE mail_send SET mailSent=UNIX_TIMESTAMP() WHERE mailID='" . $m['mailID'] . "'");
			  }
	}
	
	public function setLesebestaetigung() {
		$this->lesebestaetigung = true;
	}
	
}

?>