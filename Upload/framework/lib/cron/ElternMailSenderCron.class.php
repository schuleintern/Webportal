<?php 


/**
 * Sendet die Elternmailnachrichten
 * @author Christian
 *
 */

class ElternMailSenderCron extends AbstractCron {
	
	private $isDebug = true;
	private $sendPerRound = 20;
	
	private $mailsSent = 0;
	
	public function __construct() {
	}
	
	public function execute() {		

		if(DB::getGlobalSettings()->schulnummer != "9400") {
			
			$mails = Message::getUnsentMailsViaEMail();
			
			for($i = 0; $i < sizeof($mails); $i++) {
			    
			    if($this->mailsSent > 100) break;        // Maximal 100 Mails pro Runde vorbereiten.
			    
			    $user = $mails[$i]->getUser();
			    
			    if($user != null && $user->getEMail() != '' && $user->receiveEMail()) {
			        // Mail senden
			        
			        $betreff = DB::getGlobalSettings()->siteNamePlain . " - Neue Nachricht - " . $mails[$i]->getSubject();

			        // MailID jetzt immer mit senden, damit man auf die Nachricht mittels Mailprogramm antworten kann.
			        $betreff .= " - [" . $mails[$i]->getID() . "]";
			        
			        if($mails[$i]->needConfirmation() && !$mails[$i]->hasQuestions()) {
			            $betreff .= " {" . $mails[$i]->getConfirmationSecret() . "}";
			        }

			        $subject = $mails[$i]->getSubject();
			        
			        if($mails[$i]->getSender() != null) $sender = $mails[$i]->getSender()->getDisplayName();
			        else $sender = null;
			        
			        $attachmentsHTML = "";
			        
			        if($mails[$i]->hasAttachment()) {
			            
			            $attachments = $mails[$i]->getAttachments();
			            
			            for($a = 0; $a < sizeof($attachments); $a++) {
			                $attachmentsHTML .= "Anhang" . ($a+1) . ": <a href=\"" . DB::getGlobalSettings()->urlToIndexPHP . "?page=MessageAttachmentDownload&aid=" . $attachments[$a]->getID() . "&ac=" . $attachments[$a]->getAccessCode() . "\">" . $attachments[$a]->getUpload()->getFileName() . "</a><br />";
			            }
			        }
			        
			        $needConfirm = $mails[$i]->needConfirmation();
			        $confirmLink = DB::getGlobalSettings()->urlToIndexPHP . "?page=MessageConfirm&mailID=" . $mails[$i]->getID() . "&a=" . $mails[$i]->getConfirmationSecret();
			        
			        $unsubscribeLink = DB::getGlobalSettings()->urlToIndexPHP . "?index.php?page=userprofile";
			        
			        $messageText = $mails[$i]->getText();
			        
			        $allowAnswer = $mails[$i]->allowAnswer();
			        
			        $hasQuestions = $mails[$i]->hasQuestions();
			        
			        $questionHTML = "<ul>";
			        
			        $questions = $mails[$i]->getQuestions();
			        for($q = 0; $q < sizeof($questions); $q++) {
			            $questionHTML .= "<li>" . $questions[$q]->getQuestionText() . "</li>";
			        }
			        
			        $questionHTML .= "</ul>";

			        $replyOrForwardText = "";

			        if($mails[$i]->getReplyMessage() !== null) {
			            // Antwort Nachricht
                        $replyOrForwardText = $mails[$i]->getReplyMessage()->getText();
                    }

			        if($mails[$i]->getForwardMessage() !== null) {
			            $replyOrForwardText = $mails[$i]->getForwardMessage()->getText();
                    }

			        $myRecipient = "";

			        if($mails[$i]->getMyRecipient() !== null) {
			            $myRecipient = $mails[$i]->getMyRecipient()->getDisplayName();
                    }
			        
			        $mailHTML = "";
			        eval("\$mailHTML = \"" . DB::getTPL()->get("messages/send/emailnewmessage") . "\";");
			        
			        $mail = new email($user->getEMail(), $betreff, $mailHTML);
			        $mail->isHTML();
			        
			        if($mails[$i]->needConfirmation() && !$mails[$i]->hasQuestions()) $mail->setLesebestaetigung();
			        $mail->send();
			        $this->mailsSent++;
			    }
			   
			    
			    $mails[$i]->setSentViaMail();
			}

		}
		
		
	}
	
	public function getName() {
		return "E-Mails vorbereiten";
	}
	
	public function getDescription() {
		return "Bereitet den Versand von Benachrichtigungen an Eltern / Lehrer / Schüler und andere Personen vor.";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
		return ['success' => true, 'resultText' => $this->mailsSent . " verarbeitet."];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 120;		// alle zwei Minunten ausführen
	}
}



?>