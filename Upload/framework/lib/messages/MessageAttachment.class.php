<?php


class MessageAttachment {
	
	private $data;
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getID() {
		return $this->data['attachmentID'];
	}
	
	public function getUpload() {
		return FileUpload::getByID($this->data['attachmentFileUploadID']);
	}
	
	public function getAccessCode() {
		return $this->data['attachmentAccessCode'];
	}
	
	public function sendFile() {
		self::getUpload()->sendFile();
	}
	
	public static function getByID($id) {
		$data = DB::getDB()->query_first("SELECT * FROM messages_attachment WHERE attachmentID='" . intval($id) . "'");
	
		if($data['attachmentID'] > 0) return new MessageAttachment($data);
		else return null;
	}
	
    /**
     * 
     * @param FileUpload $upload
     * @return NULL|MessageAttachment
     */
	public static function addAttachmentAndGetObject($upload) {
		DB::getDB()->query("INSERT INTO messages_attachment (attachmentFileUploadID, attachmentAccessCode) values('" . $upload->getID() . "','" . substr(md5(rand()),0,5) . "')");
		$id = DB::getDB()->insert_id();
		
		$attachment = MessageAttachment::getByID($id);
		
		return $attachment;
	}
	
}

