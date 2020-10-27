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

		

		// if(!$this->isTeacher) {
		// 	DB::showError("Diese Seite ist leider für Sie nicht sichtbar.");
		// 	die();
		// }

		
		$acl = json_encode( $this->getAcl() );

		//$prevDays = DB::getSettings()->getValue("mensa-speiseplan-days");

		$showDays = json_encode(array(
			'Mo' => DB::getSettings()->getValue("ganztags-day-mo"),
			'Di' => DB::getSettings()->getValue("ganztags-day-di"),
			'Mi' => DB::getSettings()->getValue("ganztags-day-mi"),
			'Do' => DB::getSettings()->getValue("ganztags-day-do"),
			'Fr' => DB::getSettings()->getValue("ganztags-day-fr"),
			'Sa' => DB::getSettings()->getValue("ganztags-day-sa"),
			'So' => DB::getSettings()->getValue("ganztags-day-so")
		));
		

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