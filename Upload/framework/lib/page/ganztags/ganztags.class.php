<?php


class ganztags extends AbstractPage {
	
	private $isAdmin = false;
	private $isTeacher = false;
	


	public function __construct() {
		
		parent::__construct(array("Lehrertools", "ganztags"));
				
		$this->checkLogin();
		
		if(DB::getSession()->isTeacher()) {
			$this->isTeacher = true;
		}
		
		if(DB::getSession()->isAdmin()) $this->isTeacher = true;
		
		if(!$this->isTeacher) {
			$this->isTeacher = DB::getSession()->isMember("Webportal_Klassenlisten_Sehen");
		}
		
		if(!$this->isTeacher) {
		    $this->isTeacher = DB::getSession()->isMember("Schuelerinfo_Sehen");
		}
		
		
	}

	public function execute() {
		
		include_once("../framework/lib/phpexcel/PHPExcel.php");
				
		$today = date("d.m.Y");
		
		if(!$this->isTeacher) {
			DB::showError("Diese Seite ist leider für Sie nicht sichtbar.");
			die();
		}
		
		$schueler = schueler::getGanztagsSchueler();

		foreach($schueler as $item) {
			$html .= '<tr>';
			$html .= '<td>'.$item->getKlassenObjekt()->getKlassenName().'</td>';
			$html .= '<td>'.$item->getVornamen().'</td>';
			$html .= '<td>'.$item->getRufname().'</td>';
			$html .= '<td>'.$item->getName().'</td>';
			$html .= '<td>'.$item->getGeschlecht().'</td>';
			$html .= '</tr>';
		}
		
		eval("echo(\"" . DB::getTPL()->get("ganztags/index"). "\");");
		
	}
	
	
	public static function hasSettings() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return array();
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
		if($_REQUEST['action'] == "addListenAccess") {
			$group = usergroup::getGroupByName("Webportal_Klassenlisten_Sehen");
			$group->addUser(intval($_POST['userID']));
			header("Location: $selfURL");
			exit(0);
		}
		 
		if($_REQUEST['action'] == "removeListenAccess") {
			$group = usergroup::getGroupByName("Webportal_Klassenlisten_Sehen");
			$group->removeUser(intval($_REQUEST['userID']));
			header("Location: $selfURL");
			exit(0);
		}
		 
		$html = 'Auf die Klassenlisten haben nur Lehrer Zugriff.<br />Über die Benutzerauswahl rechts können weitere Benutzer freigegeben werden.';
		
		// $html .= 'Auf den Klassenkalender haben nur Lehrer Zugriff.';
		 
		$box = administrationmodule::getUserListWithAddFunction($selfURL, "klassenlistenzugriff", "addListenAccess", "removeListenAccess", "Benutzer mit Zugriff auf die Klassenlisten","Lehrer haben immer Zugriff. Für einen Zugriff auf die Klassenlisten ohne ein Lehrer zu sein hier die Benutzer auswählen. (Gilt vor allem für Sekretariatskräfte.)", "Webportal_Klassenlisten_Sehen");
		 
		$html = "<div class=\"row\"><div class=\"col-md-9\">$html</div><div class=\"col-md-3\">$box</div></div>";
		 
		return $html;
	}
}


?>