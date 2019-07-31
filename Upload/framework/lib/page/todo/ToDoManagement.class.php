<?php


/**
 * Noch nicht implementiert.
 * @deprecated
 */
class ToDoManagement extends AbstractPage {

	private $isSchulleitung = false;


	private $myTodos = [];

	public function __construct() {
		parent::__construct ( array (
			"Aufgaben"
		) );

		$this->checkLogin();

		// Todos sind immer verfügbar

	}

	public function execute() {
	    switch ($_REQUEST['action']) {

	        case 'getMyTodos':
	            // als JSON ausliefern
	            
	            
	        break;




	    }
	}


	public static function hasSettings() {
		return true;
	}

	public static function getSettingsDescription() {

	    return [];
	}


	public static function getSiteDisplayName() {
		return 'Aufgabenverwaltung';
	}

	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	}

	public static function hasAdmin() {
		return true;
	}

	public static function getAdminMenuGroup() {
		return "Tools";
	}

	public static function userHasAccess($user) {

		return true;
	}

	
}

?>