<?php


class ganztagsCalendar extends AbstractPage {
	
	private $isAdmin = false;
	private $isTeacher = false;
	


	public function __construct() {
		
		parent::__construct(array("Lehrertools", "Ganztags - Tagesansicht"));
				
		$this->checkLogin();
		
		// if(DB::getSession()->isTeacher()) {
		// 	$this->isTeacher = true;
		// }
		
		// if(DB::getSession()->isAdmin()) $this->isTeacher = true;
		
		// if(!$this->isTeacher) {
		// 	$this->isTeacher = DB::getSession()->isMember("Webportal_Klassenlisten_Sehen");
		// }
		
		// if(!$this->isTeacher) {
		//     $this->isTeacher = DB::getSession()->isMember("Schuelerinfo_Sehen");
		// }
		
		
	}

  public static function siteIsAlwaysActive() {
    return true;
  }
	public function execute() {
		
		if(AbstractPage::isActive('ganztags')) {

		


			
			// 		$absenzen = DB::getDB()->query("SELECT * FROM absenzen_absenzen 
			// 			LEFT JOIN schueler ON absenzSchuelerAsvID=schuelerAsvID 
			// 			WHERE absenzBeurlaubungID='" . $this->data['beurlaubungID'] . "'
			// 			ORDER BY absenzDatum");
				
			// 		$returnArray = array();
			// 		while($a = DB::getDB()->fetch_array($absenzen)) {
			// 			$returnArray[] = new Absenz($a);
			// 			//$returnArray[] = $a;
			// 		}
			
			// 		echo "<pre>";
			// print_r($returnArray);
			// echo "</pre>";

			
			
			// if(!$this->isTeacher) {
			// 	DB::showError("Diese Seite ist leider für Sie nicht sichtbar.");
			// 	die();
			// }

			$showDays = array(
				'Mo' => DB::getSettings()->getValue("ganztags-day-mo"),
				'Di' => DB::getSettings()->getValue("ganztags-day-di"),
				'Mi' => DB::getSettings()->getValue("ganztags-day-mi"),
				'Do' => DB::getSettings()->getValue("ganztags-day-do"),
				'Fr' => DB::getSettings()->getValue("ganztags-day-fr"),
				'Sa' => DB::getSettings()->getValue("ganztags-day-sa"),
				'So' => DB::getSettings()->getValue("ganztags-day-so")
			);



			if ( $_REQUEST['action'] == 'getWeek') {

				
				if ( !$_GET['bis'] ) {
					die('missing data');
				}
				if ( !$_GET['von'] ) {
					die('missing data');
				}
				if ( !$_GET['days'] ) {
					die('missing data');
				}
		
				$von = date('Y-m-d', $_GET['von']);
				$bis = date('Y-m-d', $_GET['bis']);

				// echo $von;
				// echo $bis;

				$days = $_GET['days'];

				// print_r($days);
				
				include_once("../framework/lib/data/absenzen/Absenz.class.php");




				$schueler = schueler::getGanztagsSchueler('schueler.schuelerRufname, schueler.schuelerName');
				$query = DB::getDB()->query("SELECT *  FROM ganztags_gruppen ORDER BY sortOrder, name ");
				$gruppen = [];
				while($group = DB::getDB()->fetch_array($query)) {
					$gruppen[] = $group;
				}


				foreach($days as $key => $day) {

					if (empty($day)) {
						continue;
					}
					$day = json_decode($day);

					if (!$day[0] || !$day[1]) {
						continue;
					}

					$absenzen = Absenz::getAbsenzenForDate($day[0], null);
			
					// echo "<pre>";
					// print_r($absenzen);
					// echo "</pre>";

					//$day['absenzen'] = $absenzen;


					$day_db = 'tag_'.strtolower($day[1]);
					$list_gruppen = [];
					foreach($gruppen as $gruppe) {
						$arr = [];
						$found_absenz_anz = 0;
						foreach($schueler as $item) {
							
							if ($item->getGruppe() == $gruppe['id']) {
								if ( $item->getGanztags()[$day_db] ) {

									$arr_schueler = [
										'rufname' => $item->getRufname(),
										'name' => $item->getName(),
										'geschlecht' => $item->getGeschlecht(),
										'klasse' => $item->getKlassenObjekt()->getKlassenName(),
										'absenz' => false,
										'absenz_info' => false
									];
									$found_absenz = false;
									
									foreach($absenzen as $absenz) {
										if  ( $item->getAsvID() == $absenz->getSchueler()->getAsvID() ) {
											$found_absenz = [
												"stunden" => $absenz->getStundenAsString(),
												"bemerkung" => nl2br($absenz->getBemerkung())
											];
											$found_absenz_anz++;
										}
									}

									if ($found_absenz) {
										$arr_schueler['absenz'] = true;
										$arr_schueler['absenz_info'] = $found_absenz;
									}

									$arr[] = $arr_schueler;
									
								}
								
							}
						}
						$gruppe['absenz_anz'] = $found_absenz_anz;

						$list_gruppen[] = [
							'gruppe' => $gruppe,
							'schueler' => $arr
						];
					}
					$day['gruppen'] = $list_gruppen;

					$days[$key] = $day;

				}
				
				// echo "<pre>";
				// print_r($days);
				// echo "</pre>";

				echo json_encode( $days );
				
				
				exit;

			}


			
			$acl = json_encode( $this->getAcl() );

			$showDays = json_encode($showDays);
			//$prevDays = DB::getSettings()->getValue("mensa-speiseplan-days");

			
			

			eval("echo(\"" . DB::getTPL()->get("ganztags/calendar"). "\");");
		}
	}
	
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getSettingsDescription() {
		$settings = array(
			array(
				'name' => "ganztags-day-mo",
				'typ' => "BOOLEAN",
				'titel' => "Montag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-di",
				'typ' => "BOOLEAN",
				'titel' => "Dienstag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-mi",
				'typ' => "BOOLEAN",
				'titel' => "Mittwoch anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-do",
				'typ' => "BOOLEAN",
				'titel' => "Donnerstag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-fr",
				'typ' => "BOOLEAN",
				'titel' => "Freitag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-sa",
				'typ' => "BOOLEAN",
				'titel' => "Samstag anzeigen?",
				'text' => ""
			),
			array(
				'name' => "ganztags-day-so",
				'typ' => "BOOLEAN",
				'titel' => "Sonntag anzeigen?",
				'text' => ""
			)
		);
		return $settings;
	}
	
	
	public static function getSiteDisplayName() {
		return 'Ganztags';
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
		return 'Lehrertools';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-wrench';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-table';
	}
	

	public static function displayAdministration($selfURL) {
		 
		if($_REQUEST['add'] > 0) {
			DB::getDB()->query("INSERT INTO ganztags_gruppen (`name`, `sortOrder`)
					values (
						'" . DB::getDB()->escapeString($_POST['ganztagsName']) . "',
						" . DB::getDB()->escapeString($_POST['ganztagsSortOrder']) . "
					) ");
		}

		if($_REQUEST['delete'] > 0) {
			DB::getDB()->query("DELETE FROM ganztags_gruppen WHERE id='" . $_REQUEST['delete'] . "'");
		}
		
		if($_REQUEST['save'] > 0) {
			$objekte = DB::getDB()->query("SELECT * FROM ganztags_gruppen ORDER BY sortOrder ASC");
			
			$objektData = array();
			while($o = DB::getDB()->fetch_array($objekte)) {
				DB::getDB()->query("UPDATE ganztags_gruppen SET 
						name='" . DB::getDB()->escapeString($_POST["name_".$o['id']]) . "',
						sortOrder='" . DB::getDB()->escapeString($_POST["sortOrder_".$o['id']]) . "'
						WHERE id='" . $o['id'] . "'");
			}
			
			
			header("Location: $selfURL");
			exit(0);
		}

		$html = '';

		$objekte = DB::getDB()->query("SELECT * FROM ganztags_gruppen ORDER BY sortOrder ASC");
		
		$objektData = array();
		while($o = DB::getDB()->fetch_array($objekte)) $objektData[] = $o;

		$objektHTML = "";
		for($i = 0; $i < sizeof($objektData); $i++) {
			$objektHTML .= "<tr>";
				$objektHTML .= "<td><input type=\"text\" name=\"name_" . $objektData[$i]['id'] . "\" class=\"form-control\" value=\"" . $objektData[$i]['name'] . "\"></td>";
				$objektHTML .= "<td><input type=\"number\" name=\"sortOrder_" . $objektData[$i]['id'] . "\" class=\"form-control\" value=\"" . @htmlspecialchars($objektData[$i]['sortOrder']) . "\"></td>";
				$objektHTML .= "<td><a href=\"#\" onclick=\"javascript:if(confirm('Soll das Objekt wirklisch gelöscht werden?')) window.location.href='$selfURL&delete=" . $objektData[$i]['id'] . "';\"><i class=\"fa fa-trash\"></i> Löschen</a></td>";
				
				$objektHTML .= "</tr>";
		}
	
		$html .= $objektHTML;
		eval("\$html = \"" . DB::getTPL()->get("ganztags/admin") . "\";");

		return $html;
	}
}


?>