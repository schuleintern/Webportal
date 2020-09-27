<?php


class mensaSpeiseplan extends AbstractPage {
	
	private $isAdmin = false;
	private $isTeacher = false;
	


	public function __construct() {
		
		parent::__construct(array("Mensa Speiseplan"));
				
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
			
			
			$booked_own = [];
			$booked_db = DB::getDB()->query("SELECT a.*
					FROM mensa_order as a
					WHERE a.userID = ".intval( DB::getUserID() )."" );
			while($order = DB::getDB()->fetch_array($booked_db)) {
				$booked_own[] = $order['speiseplanID'];
			}

			
			$result = DB::getDB()->query("SELECT a.*
				FROM mensa_speiseplan as a
				WHERE a.date >= '".$von."' AND a.date <= '".$bis."'" );

			

			$return = [];

			while($row = DB::getDB()->fetch_array($result)) {
				
				if ($row['desc']) {
					$row['desc'] = nl2br($row['desc']);
				}
				$row['booked'] = 0;
				if ( in_array($row['id'], $booked_own) ) {
					$row['booked'] = 1;
				}

				if ( DB::getSession()->isAdmin() || DB::getSession()->isMember(mensaSpeiseplan::getAdminGroup()) ) {

					$row['booked_all'] = [
						'list' => [],
						'schueler' => 0,
						'lehrer' => 0,
						'eltern' => 0,
						'none' => 0,
						'summe' => 0
					];

					$bookedDB = DB::getDB()->query("SELECT a.userID, a.time
						FROM mensa_order as a
						WHERE a.speiseplanID = '".$row['id']."' " );
					while($row_order = DB::getDB()->fetch_array($bookedDB)) {
						
						

						$userData = PAGE::getFactory()->getUserByID( $row_order['userID'] );
						$booked_user = new user( $userData );

						$user_Typ = '';

						if ( $booked_user->isPupil() ) {
							$row['booked_all']['schueler']++;
						}
						if ( $booked_user->isTeacher() ) {
							$row['booked_all']['lehrer']++;
						}
						if ( $booked_user->isEltern() ) {
							$row['booked_all']['eltern']++;
						}
						if ( $booked_user->isNone() ) {
							$row['booked_all']['none']++;
						}

						$row['booked_all']['list'][] = [ $row_order['userID'], $booked_user->getDisplayName(), $booked_user->getUserTyp(), $row_order['time'] ];

					}

					$row['booked_all']['summe'] = count($row['booked_all']['list']);

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


		//$acl = json_encode( $this->getAcl() );

		// echo "<pre>";
		// print_r($acl);
		// echo "</pre>";

		$prevDays = DB::getSettings()->getValue("mensa-speiseplan-days");
		
		if (!intval($prevDays)) {
			$prevDays = 0;
		}

		eval("echo(\"" . DB::getTPL()->get("mensa/index"). "\");");
		
	}

	

	
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getSettingsDescription() {
		//return array();

		$settings = array(
			array(
					'name' => "mensa-speiseplan-days",
					'typ' => "NUMMER",
					'titel' => "Wie viele Tage vorher muss gebucht werden?",
					'text' => "Default: 1"
			)
		);
		return $settings;

	}
	
	
	public static function getSiteDisplayName() {
		return 'Mensa Speiseplan';
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
		//return false;
		return 'Webportal_Mensa_Speiseplan';
	}
	
	public static function getAdminMenuGroup() {
		return 'Schulinformationen';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fas fa-utensils';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fas fa-utensils';
	}
	

	public static function displayAdministration($selfURL) {
		 
	}
}


?>