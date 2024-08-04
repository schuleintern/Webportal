<?php

class ausleihe extends AbstractPage {

	private $info;
	
	private $isAdmin = false;
		
	private $displayName = "";
	

	public function __construct() {
	    parent::__construct(array("Reservierungen", "Ausleihe"));
		
		$this->checkLogin(); 
				
		$this->displayName = self::hasCurrentUserAccess();

		
		if($this->displayName == NULL) {
			new errorPage();
		}
		
		
		if(in_array("Webportal_Reservierung_Admin",DB::getSession()->getGroupNames()) || DB::getSession()->isAdmin()) $this->isAdmin = true;
	}
	
	/**
	 * Überprüft, ob der Nutzer Zugriff hat.
	 * (Ergibt NULL, wenn kein Zugriff. Ansonsten der Name, der inm der Ausleihe angezeigt wird.)
	 * @return String|NULL
	 */
	
	public static function hasCurrentUserAccess() {
		
		$displayName = "";
		
		if(DB::getSession()->isTeacher() && DB::getSettings()->getBoolean("ausleihe-lehrer")) {
			$access = true;
			$displayName = DB::getSession()->getTeacherObject()->getKuerzel();
		}
		
		
		if(DB::getSession()->isPupil() && DB::getSettings()->getBoolean("ausleihe-schueler")) {
			$access = true;
			$displayName = DB::getSession()->getPupilObject()->getCompleteSchuelerName() . " (" . DB::getSession()->getPupilObject()->getKlasse() . ")";
		}
		
		if(DB::getSession()->isEltern() && DB::getSettings()->getBoolean("ausleihe-eltern")) {
			$access = true;
		 	$displayName= DB::getSession()->getData("userName");
		}
		
		if(!$access) {
			if(DB::getSession()->isMember('Webportal_Ausleihe_Nicht_Schulperson')) {
				$access = true;
				$displayName = DB::getSession()->getData("userName");
			}
		}
		
		if(!$access && DB::getSession()->isAdmin()) {
			$access = true;
				
			$displayName = DB::getSession()->getData("userName");
		}
		
		
		if($access) return $displayName;
		
		else return NULL;
	}

	public function execute() {
		
		// ajax from frontend!
		if(isset($_REQUEST['action'])) {

			if ( $_REQUEST['action'] == 'getWeek') {
				$return = $this->getWeek();
				echo json_encode( $return );
				exit;
			}

			if ( $_REQUEST['action'] == 'setEvent') {
				$return = $this->setEvent();
				echo json_encode( $return );
				exit;
			}

			if ( $_REQUEST['action'] == 'deleteEvent') {
				$this->deleteEvent();
				exit;
			}

			if ( $_REQUEST['action'] == 'checkEvent') {
				$return = $this->checkEvent();
				echo json_encode( $return );
				exit;
			}
			
			if ( $_REQUEST['action'] == 'myDates') {
				$return = $this->myDates();
				echo json_encode( $return );
				exit;
			}

			// Immer Exit wenn GET-action
			exit;
		}

		// SQL get Objects
		$objects = array();
		$objectsEasy = array();
		$data = DB::getDB()->query("SELECT * FROM ausleihe_objekte WHERE
			isActive = 1 && objektAnzahl > 0 ORDER BY sortOrder ");

		while($a = DB::getDB()->fetch_array($data)) {

			if ( (int)$a['objektAnzahl'] && (int)$a['sumItems'] ) {

				$diff = (int)$a['objektAnzahl'] / (int)$a['sumItems'];

				for($i = 0; $i < $diff; $i++) {

					$sub = $i +1;
					$arr = array(
						'objektID' => $a['objektID'],
						'objektName' => $a['objektName'].' ('.$sub.'/'.$diff.')',
						'objektAnzahl' => $a['objektAnzahl'],
						'sumItems' => $a['sumItems'],
						'sub' => $sub
					);
					$objects[] = $arr;
				}

			} else {

				$arr = array(
					'objektID' => $a['objektID'],
					'objektName' => $a['objektName'],
					'objektAnzahl' => $a['objektAnzahl'],
					'sumItems' => $a['sumItems']
				);
				$objects[] = $arr;
			}

			
			$arrEasy = array(
				'objektID' => $a['objektID'],
				'objektName' => $a['objektName'],
				'objektAnzahl' => $a['objektAnzahl'],
				'sumItems' => $a['sumItems']
			);
			$objectsEasy[] = $arrEasy;
			
		}	
		// echo "<pre>";
		// print_r($objects);
		// echo "</pre>";

		$objects = json_encode($objects, true);
		$objectsEasy = json_encode($objectsEasy, true);

		eval("echo(\"" .DB::getTPL()->get("ausleihe/ausleihe_ausleihe") . "\");");

	}

	private function getWeek() {

		if ( !$_GET['bis'] ) {
			die('missing data');
		}
		if ( !$_GET['von'] ) {
			die('missing data');
		}

		$von = date('Y-m-d', $_GET['von']);
		$bis = date('Y-m-d', $_GET['bis']);

		$result = array();

		$q = "SELECT a.*, b.objektName, b.objektAnzahl, b.sumItems
			FROM ausleihe_ausleihe as a 
			LEFT JOIN ausleihe_objekte as b
			ON a.ausleiheObjektID = b.objektID
			WHERE ausleiheDatum >= '$von'
			AND ausleiheDatum <= '$bis'
			";


		if ($_GET['filter'] != 'false') {
			$filter = json_decode($_GET['filter']);

			// echo "###<pre>";
			// print_r($filter);
			// echo "</pre>";

			if ( isset($filter->object) && isset($filter->object->objektID) && $filter->object->objektID ) {
				$q .= " AND ausleiheObjektID = ".(int)$filter->object->objektID;
			}
	
		}

		$data = DB::getDB()->query($q);

		while($a = DB::getDB()->fetch_array($data)) {

			$sum = 0;
			$sub = array();
			if ( (int)$a['sumItems'] && (int)$a['objektAnzahl'] ) {
				$num = ( (int)$a['sumItems'] * $a['ausleiheObjektIndex'] ) + 1;
				for ($i = 0; $i < (int)$a['sumItems'] ; $i++) {
					array_push($sub, $num);
					$num++;
				}
				$sum = (int)$a['objektAnzahl'] / (int)$a['sumItems'];
			}

			$result[] = array(
				'ausleiheID' => $a['ausleiheID'],
				'ausleiheDatum' => $a['ausleiheDatum'],
				'ausleiheStunde' => $a['ausleiheStunde'],
				'ausleiheObjektID' => $a['ausleiheObjektID'],
				'ausleiheKlasse' => $a['ausleiheKlasse'],
				'ausleiheLehrer' => $a['ausleiheLehrer'],
				'ausleiheAusleiherUserID' => $a['ausleiheAusleiherUserID'],
				'objektName' => $a['objektName'],
				'sub' => $sub,
				'part' => $a['ausleiheObjektIndex'] +1,
				'sum' => $sum
			);
		}	

		// echo "###<pre>";
		// print_r($result);
		// echo "</pre>";

		return $result;
	}

	private function setEvent() {

		// echo "###<pre>";
		// print_r($_POST);
		// echo "</pre>";

		if ( !$_POST['objektID'] ) {
			echo json_encode( array('error'=>true,'errorMsg'=>'Bitte wählen Sie ein Objekt aus.') , true);
			exit;
		}
		if (!$this->displayName
			|| !$_POST['datum']
			|| !$_POST['objektID']
			|| !$_POST['stunde']
			|| !$_POST['klasse']) {
			echo json_encode( array('error'=>true,'errorMsg'=>'Missing Data!') , true);
			exit;
		}

		$sub = 0;
		if ($_POST['sub'] != 'false') {
			$sub = (int)$_POST['sub'] -1;
		}
		

		if ( !DB::getDB()->query("INSERT INTO ausleihe_ausleihe
				(
					ausleiheObjektID,
					ausleiheObjektIndex,
					ausleiheDatum,
					ausleiheLehrer,
					ausleiheStunde,
					ausleiheKlasse
				) 
				values(
					'" . $_POST['objektID'] . "',
					'" . $sub . "',
					'" . $_POST['datum'] . "',
					'" . $this->displayName . "',
					'" . $_POST['stunde'] . "',
					'" . $_POST['klasse'] . "'
				)
		") ) {
			echo json_encode( array('error'=>true,'errorMsg'=>'Fehler beim Hinzufügen!') , true);
			exit;
		}

		return array('insert' => true);
		
	}

	private function deleteEvent() {

		$ausleiheID = $_GET['ausleiheID'];
		if (!$ausleiheID) {
			echo json_encode( array('error'=>true,'errorMsg'=>'Missing Data!') , true);
			exit;
		}

		$o = DB::getDB()->query_first("SELECT * FROM ausleihe_ausleihe
		WHERE ausleiheID='" . $ausleiheID . "' ");
		if( !$o['ausleiheID'] ) {
			echo json_encode( array('error'=>true,'errorMsg'=>'Missing Ausleihe id:'.$ausleiheID) , true);
			exit;
		}

		if(strtolower($o['ausleiheLehrer']) == strtolower($this->displayName) || $this->isAdmin) {
			if ( DB::getDB()->query("DELETE FROM ausleihe_ausleihe WHERE ausleiheID='" . $ausleiheID . "'") ) {
				echo json_encode( array('delete' => true), true);
				exit;
			}
		} else {
			echo json_encode( array('error'=>true,'errorMsg'=>'Missing Rights!') , true);
			exit;
		}
	}

	// private function checkEvent() {

	// 	$date = explode(',',$_POST['datum'])[1];

	// 	if (!$_POST['datum']
	// 		|| !$_POST['stunde']
	// 		|| !$date) {
	// 		echo json_encode( array('error'=>true,'errorMsg'=>'Missing Data!') , true);
	// 		exit;
	// 	}

	// 	$objects = array();
	// 	$data = DB::getDB()->query("SELECT a.ausleiheID, a.ausleiheObjektID, a.ausleiheObjektIndex, b.objektAnzahl, b.sumItems
	// 		FROM ausleihe_ausleihe as a
	// 		LEFT JOIN ausleihe_objekte as b
	// 		ON a.ausleiheObjektID = b.objektID
	// 		WHERE ausleiheDatum = '".$date."'
	// 		AND ausleiheStunde = ".(int)$_POST['stunde']."
	// 	");

	// 	$return = array('check' => false, 'objects' =>array());
	// 	while($a = DB::getDB()->fetch_array($data)) {
	// 		if ($a['ausleiheID']) {
	// 			$return['check'] = true;
	// 			$arr = array(
	// 				'ausleiheID' => $a['ausleiheID'],
	// 				'ausleiheObjektID' => $a['ausleiheObjektID'],
	// 				'sub' => 0
	// 			);
	// 			if ( (int)$a['objektAnzahl'] && (int)$a['sumItems']) {
	// 				$arr['sub'] = $a['ausleiheObjektIndex'] +1;
	// 			}
	// 			array_push($return['objects'], $arr );
	// 		}
	// 	}	

	// 	return $return;
		
	// }
	
	private function myDates() {

		$return = array();

		// SQL get user next events
		$today = date('Y-m-d', time());

		$data = DB::getDB()->query("SELECT a.*, b.objektName, b.objektAnzahl, b.sumItems
			FROM ausleihe_ausleihe as a 
			LEFT JOIN ausleihe_objekte as b
			ON a.ausleiheObjektID = b.objektID
			WHERE a.ausleiheLehrer = '$this->displayName'
			AND a.ausleiheDatum >= '$today'
			ORDER BY a.ausleiheDatum 
		");

		while($a = DB::getDB()->fetch_array($data)) {
			$date = new DateTime($a['ausleiheDatum']);

			$sum = 0;
			$sub = array();
			if ( (int)$a['sumItems'] && (int)$a['objektAnzahl'] ) {
				$num = ( (int)$a['sumItems'] * $a['ausleiheObjektIndex'] ) + 1;
				for ($i = 0; $i < (int)$a['sumItems'] ; $i++) {
					array_push($sub, $num);
					$num++;
				}
				$sum = (int)$a['objektAnzahl'] / (int)$a['sumItems'];
			}

			$return[] = array(
				'ausleiheID' => $a['ausleiheID'],
				'ausleiheDatum' => $date->format('d.m.Y D.'),
				'ausleiheStunde' => $a['ausleiheStunde'],
				'objektName' => $a['objektName'],
				'ausleiheKlasse' => $a['ausleiheKlasse'],
				'ausleiheObjektIndex' => $a['ausleiheObjektIndex'],
				'objektName' => $a['objektName'],
				'sub' => $sub,
				'part' => $a['ausleiheObjektIndex'] +1,
				'sum' => $sum
			);
		}

		return $return;
		
	}


	
	public static function hasSettings() {
		return true;
	}
	
	
	public static function getSiteDisplayName() {
		return 'Reservierung (Objekte)';
	}
	

	
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return [
			[
				'name' => "ausleihe-lehrer",
				'typ' => 'BOOLEAN',
				'titel' => "Zugriff auf das Ausleihmodul durch Lehrer?",
				'text' => ""
			],
			[
				'name' => "ausleihe-schueler",
				'typ' => 'BOOLEAN',
				'titel' => "Zugriff auf das Ausleihmodul durch Schüler?",
				'text' => ""
			],
			[
				'name' => "ausleihe-eltern",
				'typ' => 'BOOLEAN',
				'titel' => "Zugriff auf das Ausleihmodul durch Eltern?",
				'text' => ""
			]
		];
	}
	
	public static function getUserGroups() {
		return array();
	}
	
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Reservierung_Admin';
	}
	
	public static function displayAdministration($selfURL) {
		
		$group = usergroup::getGroupByName('Webportal_Ausleihe_Nicht_Schulperson');
		
		if($_REQUEST['action'] == 'addUser') {
			$group->addUser(intval($_REQUEST['userID']));
		}
		if($_REQUEST['action'] == 'removeUser') {
			$group->removeUser(intval($_REQUEST['userID']));
		}
		
		
		if($_REQUEST['add'] > 0) {
			DB::getDB()->query("INSERT INTO ausleihe_objekte (objektName, objektAnzahl, isActive, sortOrder, sumItems)
					values(
						'" . DB::getDB()->escapeString($_POST['objektName']) . "',
						'" . DB::getDB()->escapeString($_POST['objektAnzahl']) . "',
						1,
						'" . DB::getDB()->escapeString($_POST['objektSortOrder']) . "',
						'" . DB::getDB()->escapeString($_POST['sumItems']) . "')
					");
		}
		
		if($_REQUEST['delete'] > 0) {
			DB::getDB()->query("DELETE FROM ausleihe_objekte WHERE objektID='" . $_REQUEST['delete'] . "'");
			DB::getDB()->query("DELETE FROM ausleihe_ausleihe WHERE ausleiheObjektID='" . $_REQUEST['delete'] . "'");
		}
		
		if($_REQUEST['save'] > 0) {
			$objekte = DB::getDB()->query("SELECT * FROM ausleihe_objekte ORDER BY sortOrder ASC");
			
			$objektData = array();
			while($o = DB::getDB()->fetch_array($objekte)) {
				DB::getDB()->query("UPDATE ausleihe_objekte SET 
						objektName='" . DB::getDB()->escapeString($_POST['objektName_' . $o['objektID']]) . "',
						objektAnzahl='" . DB::getDB()->escapeString($_POST['objektAnzahl_' . $o['objektID']]) . "',
						sumItems='" . DB::getDB()->escapeString($_POST['sumItems_' . $o['objektID']]) . "',
						sortOrder='" . DB::getDB()->escapeString($_POST['sortOrder_' . $o['objektID']]) . "'
						WHERE objektID='" . $o['objektID'] . "'");
			}
			
			
			header("Location: $selfURL");
			exit(0);
		}
		
		
		$ausleiheZugriff = administrationmodule::getUserListWithAddFunction($selfURL, 'ausleiheuser', 'addUser', 'removeUser', 'Benutzerzugriff auf das Ausleihemodul', 'Die hier angegebenen Benutzer haben Zugriff auf das Ausleihemodul zur Ausleihe der Objekte. (In den Einstellungen können dür die Gruppen Lehrer / Schüler und Eltern pauschale Freigaben erteilt werden.)', 'Webportal_Ausleihe_Nicht_Schulperson');
		
		
		$objekte = DB::getDB()->query("SELECT * FROM ausleihe_objekte ORDER BY sortOrder ASC");
		
		$objektData = array();
		while($o = DB::getDB()->fetch_array($objekte)) $objektData[] = $o;
		
		$objektHTML = "";
		for($i = 0; $i < sizeof($objektData); $i++) {
			$objektHTML .= "<tr>";
				$objektHTML .= "<td><input type=\"text\" name=\"objektName_" . $objektData[$i]['objektID'] . "\" class=\"form-control\" value=\"" . $objektData[$i]['objektName'] . "\"></td>";
				$objektHTML .= "<td><input type=\"number\" name=\"objektAnzahl_" . $objektData[$i]['objektID'] . "\" class=\"form-control\" value=\"" . @htmlspecialchars($objektData[$i]['objektAnzahl']) . "\"></td>";
				$objektHTML .= "<td><input type=\"number\" name=\"sumItems_" . $objektData[$i]['objektID'] . "\" class=\"form-control\" value=\"" . @htmlspecialchars($objektData[$i]['sumItems']) . "\"></td>";
				$objektHTML .= "<td><input type=\"number\" name=\"sortOrder_" . $objektData[$i]['objektID'] . "\" class=\"form-control\" value=\"" . @htmlspecialchars($objektData[$i]['sortOrder']) . "\"></td>";
				$objektHTML .= "<td><a href=\"#\" onclick=\"javascript:if(confirm('Soll das Objekt wirklisch gelöscht werden?')) window.location.href='$selfURL&delete=" . $objektData[$i]['objektID'] . "';\"><i class=\"fa fa-trash\"></i> Löschen</a></td>";
				
				$objektHTML .= "</tr>";
		}
		
		$html = "";
		
		eval("\$html = \"" . DB::getTPL()->get("ausleihe/admin") . "\";");
		
		return $html;
	}
	
	public static function getAdminMenuGroup() {
		return 'Kleinere Module';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-file-o';
	}
	
	
	public static function getActionSchuljahreswechsel() {
		return 'Ausleihen aus dem alten Schuljahr löschen';
	}
	
	public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {

        DB::getDB()->query("DELETE FROM ausleihe_ausleihe WHERE ausleiheDatum < '" . $sqlDateFirstSchoolDay . "'");

    }
}

