<?php


class mensa extends AbstractPage {
	
	private $isAdmin = false;
	private $isTeacher = false;
	


	public function __construct() {
		
		parent::__construct(array("Mensa"));
				
		$this->checkLogin();
		
		
	}

	public function execute() {
		

		if ( $_REQUEST['action'] == 'getWeek') {
			
			if ( !$_GET['bis'] ) {
				die('missing data');
			}
			if ( !$_GET['von'] ) {
				die('missing data');
			}
	
			$von = date('Y-m-d', $_GET['von']);
			$bis = date('Y-m-d', $_GET['bis']);
			
			
			$booked = [];
			$booked_db = DB::getDB()->query("SELECT a.*
				FROM mensa_order as a
				WHERE a.userID = ".intval( DB::getUserID() )."" );
			while($order = DB::getDB()->fetch_array($booked_db)) {
				$booked[] = $order['speiseplanID'];
			}

			// echo "SELECT a.*
			// FROM mensa_order as a
			// WHERE a.userID = ".intval( DB::getUserID() )."";

			// print_r($booked);
			// exit;

			$result = DB::getDB()->query("SELECT a.*
				FROM mensa_speiseplan as a
				WHERE a.date >= '".$von."' AND a.date <= '".$bis."'" );

			$return = [];

			while($row = DB::getDB()->fetch_array($result)) {
				
				if ($row['desc']) {
					$row['desc'] = nl2br($row['desc']);
				}
				$row['booked'] = 0;
				if ( in_array($row['id'], $booked) ) {
					$row['booked'] = 1;
				}
				$return[] = $row;
			}
	
			if(count($return) > 0) {
	
				echo json_encode( $return );
	
			} else {
				echo json_encode([
					'error' => true,
					'msg' => 'Es konnte kein Essen geladen werden!'
				]);
			}

			exit;
		}


		eval("echo(\"" . DB::getTPL()->get("mensa/index"). "\");");
		
	}

	
	
	
	
	public static function hasSettings() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Mensa';
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
	
	public static function getAdminGroup() {
		return false;
		//return 'Webportal_Klassenlisten_Admin';
	}
	
	public static function getAdminMenuGroup() {
		return 'Schulinformationen';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fas fa-utensils';
	}
	
	public static function getAdminMenuIcon() {
		return 'fas fa-utensils';
	}
	

	public static function displayAdministration($selfURL) {
		 
	}
}


?>