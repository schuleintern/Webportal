<?php 


class MessageSender{

	private $subject;
	private $text;
	private $sender;
	
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
	
	public function send() {
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
		  'messageQuestionIDs'
		];
		
		$saveStrings = [];
		
		$messageQuestionIDs = [];
		
		for($i = 0; $i < sizeof($this->messageQuestions); $i++) $messageQuestionIDs[] = $this->messageQuestions[$i]->getID();
		
		$messageQuestionIDs = implode(";",$messageQuestionIDs);

		$recipientNames = [];

		for($i = 0; $i < sizeof($this->recipients); $i++) {
			$recipientNames[] = DB::getDB()->encodeString($this->recipients[$i]->getDisplayName());
		} 
		

		$saveStringsRecipients = $this->sendToRecipientsAndGetSaveStrings($this->recipients, $messageQuestionIDs);
		$saveStringsCCRecipients = $this->sendToRecipientsAndGetSaveStrings($this->ccRecipients, $messageQuestionIDs);
		$saveStringsBCCRecipients = $this->sendToRecipientsAndGetSaveStrings($this->bccRecipients, $messageQuestionIDs);
		
		
		// SaveIDs mit Message IDs zusammenstellen
		
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
          '" . $messageQuestionIDs . "'
					)
				";
		DB::getDB()->query("INSERT INTO messages_messages (" . implode(",", $fields) . ") VALUES " . implode(",",$insert));
		
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
	        'messageQuestionIDs'
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
                    '" . $messageQuestionIDs . "'
					)
				";
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

