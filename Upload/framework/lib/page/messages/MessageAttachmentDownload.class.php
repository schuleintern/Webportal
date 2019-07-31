<?php

class MessageAttachmentDownload extends AbstractPage {
	
	public function __construct() {
		parent::__construct(array("Nachrichten"));
	}
	
	public function execute() {
		$attachment = MessageAttachment::getByID($_REQUEST['aid']);
		
		if($attachment != null) {
			if($attachment->getAccessCode() == $_REQUEST['ac']) {
				$attachment->sendFile();
				exit(0);
			}
		}
		
		new errorPage();
	}
	
	public static function getSettingsDescription() {
		$settings = [];
		
		
		return $settings;
	}
	
	public static function getSiteDisplayName() {
		return "Nachrichten - Download Attachment";
	}
	
	public static function hasSettings() {
		return false;
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
	
	public static function hasAdmin() {
		return false;
	}
	
	public static function getAdminGroup() {
		return "NONE";
	}
	
	public static function displayAdministration($selfURL) {
		
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-info-circle';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-info-circle';
	}
	
	public static function getAdminMenuGroup() {
		return 'Schulinformationen';
	}
}


?>