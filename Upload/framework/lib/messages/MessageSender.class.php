<?php 


class MessageSender{

	private $subject;
	private $text;
	private $sender;

    /**
     * Anzahl der verschickten Nachrichten
     * @var int
     */
	private $sentMessages = 0;
	
	/**
	 * 
	 * @var MessageRecipient[]
	 */
	private $recipients = [];
	
	private $recipientHandler = null;
	
	/**
	 * 
	 * @var MessageRecipient[]
	 */
	private $ccRecipients = [];
	
	/**
	 * 
	 * @var RecipientHandler
	 */
	private $ccRecipientHandler = null;
	
	/**
	 * 
	 * @var MessageRecipient[]
	 */
	private $bccRecipients = [];
	
	/**
	 * 
	 * @var RecipientHandler
	 */
	private $bccRecipientHandler = null;
	
	/**
	 * 
	 * @var boolean
	 */
	private $needConfirmation = false;
	
	/**
	 * Attachments
	 * @var int[]
	 */
	private $attachments = [];
	
	
	/**
	 * 
	 * @var Message
	 */
	private $replyMessage = null;

	private $forwardMessage = null;
	
	private $priority = 'NORMAL';
	
	private $allowAnswer = true;

    /**
     * Vertrauliche Nachricht? (Wird nur ohne Text per E-Mail verschickt und entsprechend angezeigt.)
     * @var bool
     */
	private $messageIsConfidential = false;
	
	/**
	 * 
	 * @var MessageQuestion[]
	 */
	private $messageQuestions = [];
	
	public function __construct() {
		$this->ccRecipientHandler = new RecipientHandler("");
		$this->bccRecipientHandler = new RecipientHandler("");
	}
	
	public function setPriority($p) {
		
		switch($p) {
			case 'low': $this->priority = 'LOW'; break;
			case 'high': $this->priority = 'HIGH'; break;
		}
	}
	
	public function dontAllowAnswer() {
		$this->allowAnswer = false;
	}

    /**
     * Setzt die Nachricht auf Vertarulich.
     */
	public function setConfidential() {
	    $this->messageIsConfidential = true;
    }
	
	/**
	 * 
	 * @param RecipientHandler $recipientHandler
	 */
	public function setRecipients($recipientHandler) {
		$this->recipientHandler= $recipientHandler;
		$this->recipients = $recipientHandler->getAllRecipients();
	}
	
	/**
	 * 
	 * @param RecipientHandler $recipientHandler
	 */
	public function setCCRecipients($recipientHandler) {
	    $this->ccRecipientHandler = $recipientHandler;
	    $this->ccRecipients = $this->ccRecipientHandler->getAllRecipients();
	}
	
	/**
	 * 
	 * @param RecipientHandler $recipientHandler
	 */
	public function setBCCRecipients($recipientHandler) {
	    $this->bccRecipientHandler = $recipientHandler;
	    $this->bccRecipients = $this->bccRecipientHandler->getAllRecipients();
	}
	
	/**
	 * 
	 * @param String $text
	 */
	public function setText($text) {
		$this->text = DB::getDB()->escapeString($text);
	}
	
	
	/**
	 * 
	 * @param MessageAttachment $attachment
	 */
	public function addAttachment($attachment) {
		$this->attachments[] = $attachment->getID();
	}
	
	public function setSubject($subject) {
		$this->subject = DB::getDB()->escapeString($subject);
	}
	
	public function setNeedConfirmation() {
		$this->needConfirmation = true;
	}
	
	/**
	 * 
	 * @param user $user
	 */
	public function setSender($user) {
		$this->sender = $user;
	}
	
	/**
	 * 
	 * @param Message $message
	 */
	public function setReplyMessage($message) {
		$this->replyMessage = $message;
	}

	public function setForwardMessage($message) {
		$this->forwardMessage = $message;
	}
	
	/**
	 * 
	 * @param MessageQuestion $question
	 */
	public function addQuestion($question) {
	    $this->needConfirmation = true;
	    $this->messageQuestions[] = $question;
	}

    public function save($folder = 'GESENDETE') {


        $fields = [
            'messageUserID',
            'messageSubject',
            'messageText',
            'messageSender',
            'messageRecipients',
            'messageRecipientsPreview',
            'messageCCRecipients',
            'messageBCCRecipients',
            'messageTime',
            'messageIsReplyTo',
            'messageIsForwardFrom',
            'messageFolder',
            'messageIsRead',
            'messageNeedConfirmation',
            'messageConfirmSecret',
            'messageAttachments',
            'messagePriority',
            'messageAllowAnswer',
            'messageHasQuestions',
            'messageQuestionIDs',
            'messageIsConfidential'
        ];

        $saveStrings = [];

        $messageQuestionIDs = [];

        for($i = 0; $i < sizeof($this->messageQuestions); $i++) {
            $messageQuestionIDs[] = $this->messageQuestions[$i]->getID();
        }

        $messageQuestionIDs = implode(";",$messageQuestionIDs);

        $recipientNames = [];

        for($i = 0; $i < sizeof($this->recipients); $i++) {
            $recipientNames[] = DB::getDB()->encodeString($this->recipients[$i]->getDisplayName());
        }

        //$saveStringsRecipients = $this->sendToRecipientsAndGetSaveStrings($this->recipients, $messageQuestionIDs);
        //$saveStringsCCRecipients = $this->sendToRecipientsAndGetSaveStrings($this->ccRecipients, $messageQuestionIDs);
        //$saveStringsBCCRecipients = $this->sendToRecipientsAndGetSaveStrings($this->bccRecipients, $messageQuestionIDs);

        // Nachricht speichern
        $insert = [];
        $insert[] = "
					('" . $this->sender->getUserID() . "',
					'" . $this->subject . "',
					'" . $this->text . "',
					'" . $this->sender->getUserID() . "',
					'',
					'" . implode(", ", $recipientNames ) . "',
                    '',
                    '',
					UNIX_TIMESTAMP(),
					'" . (($this->replyMessage != null) ? ($this->replyMessage->getID()) : 0) . "',
					'" . (($this->forwardMessage != null) ? ($this->forwardMessage->getID()) : 0) . "',
					'".(string)$folder."',
					1,
					" . (($this->needConfirmation) ? 1 : 0) . ",
					'',
					'" . implode(",",$this->attachments) . "',
					'" . $this->priority . "',
					'" . ($this->allowAnswer ? 1 : 0) . "',
                    '" . ((sizeof($this->messageQuestions) > 0) ? 1 : 0) . "',
                    '" . $messageQuestionIDs . "',
                    " . ($this->messageIsConfidential ? $this->messageIsConfidential : 0) . "
					)
				";

        if ( DB::getDB()->query("INSERT INTO messages_messages (" . implode(",", $fields) . ") VALUES " . implode(",",$insert)) ) {
            return true;
        }
        return false;

    }




	public function send($onlyOnce = false) {
		$fields = [
			'messageUserID',
			'messageSubject',
			'messageText',
			'messageSender',
			'messageRecipients',
            'messageRecipientsPreview',
		    'messageCCRecipients',
		    'messageBCCRecipients',
			'messageTime',
			'messageIsReplyTo',
			'messageIsForwardFrom',
			'messageFolder',
			'messageIsRead',
			'messageNeedConfirmation',
			'messageConfirmSecret',
			'messageAttachments',
			'messagePriority',
			'messageAllowAnswer',
		    'messageHasQuestions',
		    'messageQuestionIDs',
            'messageIsConfidential'
		];
		

		$messageQuestionIDs = [];
		for($i = 0; $i < sizeof($this->messageQuestions); $i++) {
            $messageQuestionIDs[] = $this->messageQuestions[$i]->getID();
        }
		$messageQuestionIDs = implode(";",$messageQuestionIDs);

		$recipientNames = [];
		for($i = 0; $i < sizeof($this->recipients); $i++) {
			$recipientNames[] = DB::getDB()->encodeString($this->recipients[$i]->getDisplayName());
		}

		$sumRecipients = $this->sentMessages;

        $saveStringsRecipients = $this->sendToRecipientsAndGetSaveStrings($this->recipients, $messageQuestionIDs);
        $saveStringsCCRecipients = $this->sendToRecipientsAndGetSaveStrings($this->ccRecipients, $messageQuestionIDs);
        $saveStringsBCCRecipients = $this->sendToRecipientsAndGetSaveStrings($this->bccRecipients, $messageQuestionIDs);


		// Gesendete Nachricht einfügen
		$insert = [];
		$insert[] = "
					('" . $this->sender->getUserID() . "',
					'" . $this->subject . "',
					'" . $this->text . "',
					'" . $this->sender->getUserID() . "',
					'" . implode(";",$saveStringsRecipients) . "',
					'" . implode(", ", $recipientNames ) . "',
                    '" . implode(";",$saveStringsCCRecipients) . "',
                    '" . implode(";",$saveStringsBCCRecipients) . "',
					UNIX_TIMESTAMP(),
					'" . (($this->replyMessage != null) ? ($this->replyMessage->getID()) : 0) . "',
					'" . (($this->forwardMessage != null) ? ($this->forwardMessage->getID()) : 0) . "',
					'GESENDETE',1," . (($this->needConfirmation) ? 1 : 0) . ",
					'',
					'" . implode(",",$this->attachments) . "',
					'" . $this->priority . "',
					'" . ($this->allowAnswer ? 1 : 0) . "',
                    '" . ((sizeof($this->messageQuestions) > 0) ? 1 : 0) . "',
                    '" . $messageQuestionIDs . "',
                    " . ($this->messageIsConfidential ? $this->messageIsConfidential : 0) . "
					)
				";
		DB::getDB()->query("INSERT INTO messages_messages (" . implode(",", $fields) . ") VALUES " . implode(",",$insert));

        //$messageGroupID = DB::getDB()->insert_id();




		$maxRecipientsForAutoresponder = DB::getSettings()->getInteger("messages-max-recipients-for-autoresponder");

		/*
		 * AutoResponse
		*/
		if ($onlyOnce == false && ($maxRecipientsForAutoresponder > 0 && $sumRecipients <= $maxRecipientsForAutoresponder)) {
			$this->sendAutoResponse($this->recipients);
            $this->sendAutoResponse($this->bccRecipients);
            $this->sendAutoResponse($this->ccRecipients);
		}


	}
	

	/**
	 * 
	 * Auto Response Mails
	 */
	private function sendAutoResponse($recipients) {

		for($i = 0; $i < sizeof($recipients); $i++) {

			$users = $recipients[$i]->getRecipientUserIDs();
			for($u = 0; $u < sizeof($users); $u++) {

				$d = DB::getDB()->query_first("SELECT userAutoresponse, userAutoresponseText FROM users WHERE userID=" . $users[$u]);

				if ( $d['userAutoresponse'] == 1 ) {
					// Send Autorespondermail

					$messageAutoresponse = new MessageSender();

					$sender = user::getUserByID($users[$u]);

					$messageAutoresponse->setSender($sender);
					
					$messageAutoresponse->setSubject('Abwesenheitsnotiz!');
					$messageAutoresponse->setText( nl2br($d['userAutoresponseText']) );

					$to = new UserRecipient($this->sender);

					$recipientHandler = new RecipientHandler( $to->getSaveString() );
					$messageAutoresponse->setRecipients($recipientHandler);				

					$messageAutoresponse->dontAllowAnswer();
					$messageAutoresponse->send(true);

				}
			}
		}

	}


	/**
	 * 
	 * @param MessageRecipient[] $recipients
	 * @param int[] $messageQuestionIDs
	 * @return string[] Save Strings der einzelnen Empfänger
	 */
	private function sendToRecipientsAndGetSaveStrings($recipients, $messageQuestionIDs) {
	    
	    $fields = [
	        'messageUserID',
	        'messageSubject',
	        'messageText',
	        'messageSender',
	        'messageRecipients',
	        'messageCCRecipients',
	        'messageBCCRecipients',
	        'messageTime',
            'messageIsReplyTo',
			'messageIsForwardFrom',
	        'messageFolder',
	        'messageIsRead',
	        'messageNeedConfirmation',
	        'messageConfirmSecret',
	        'messageAttachments',
	        'messagePriority',
	        'messageAllowAnswer',
	        'messageHasQuestions',
	        'messageQuestionIDs',
            'messageMyRecipientSaveString',
            'messageIsConfidential'
	    ];
	    
	    for($i = 0; $i < sizeof($recipients); $i++) {
	        
	        $insert = [];
	        
	        $saveString = $recipients[$i]->getSaveString();
	        
	        $saveString .= "[";
	        
	        $users = $recipients[$i]->getRecipientUserIDs();
	        
	        for($u = 0; $u < sizeof($users); $u++) {
	            $insert[] = "
					('" . $users[$u] . "',
					'" . $this->subject . "',
					'" . $this->text . "',
					'" . $this->sender->getUserID() . "',
					'" . $this->recipientHandler->getSaveCompleteSaveString() . "',
                    '" . $this->ccRecipientHandler->getSaveCompleteSaveString() . "',
                    '" . $this->bccRecipientHandler->getSaveCompleteSaveString() . "',
					UNIX_TIMESTAMP(),
					'" . (($this->replyMessage != null) ? ($this->replyMessage->getID()) : 0) . "',
					'" . (($this->forwardMessage != null) ? ($this->forwardMessage->getID()) : 0) . "',
					'POSTEINGANG',0," . ($this->needConfirmation ? 1 : 0) . ",
					'" . substr(md5(rand()),0,9) . "',
					'" . implode(",",$this->attachments) . "',
					'" . $this->priority . "',
					'" . ($this->allowAnswer ? 1 : 0). "',
                    '" . ((sizeof($this->messageQuestions) > 0) ? 1 : 0) . "',
                    '" . $messageQuestionIDs . "',
                    '" . $recipients[$i]->getSaveString() . "',
                    " . ($this->messageIsConfidential ? $this->messageIsConfidential : 0) . "
					)
				";
	            $this->sentMessages++;

                // TODO: PUSH
                PUSH::send( $users[$u] );

	        }
	        
	        if(sizeof($insert) > 0) {
	            DB::getDB()->query("INSERT INTO messages_messages (" . implode(",", $fields) . ") VALUES " . implode(",",$insert));
            }
	        
	        $firstNewID = DB::getDB()->insert_id();
	        
	        $messageIDs = [];
	        for($u = 0; $u < sizeof($users); $u++) {
	            $messageIDs[] = $firstNewID;
	            $firstNewID++;
	        }
	        
	        $saveString .= implode(",",$messageIDs) . "]";
	        
	        $saveStrings[] = $saveString;
	        
	    }
	    
	    return $saveStrings;
	}
}

