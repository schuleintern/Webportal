<?php


class ganztagsEdit extends AbstractPage {
	

	public function __construct() {
		
		parent::__construct(array("Lehrertools", "ganztagsEdit"));
				
		
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
		
		if(isset($_REQUEST['save'])) {

			if (!$_POST['asvid']) {
				return false;
			}

			$schueler_query = DB::getDB()->query("SELECT * FROM ganztags_schueler WHERE asvid = '".DB::getDB()->escapeString($_POST['asvid'])."' ");
			$schueler = false;
			while($row = mysqli_fetch_array($schueler_query)) { $schueler = $row; }
			
			if ($schueler['asvid']) {

				DB::getDB()->query("UPDATE ganztags_schueler SET 
					info = '" . DB::getDB()->escapeString($_POST['info']) . "',
					gruppe = " . (int)$_POST['gruppe'] . ",
					tag_mo = " . (int)$_POST['tag_mo'] . ",
					tag_di = " . (int)$_POST['tag_di'] . ",
					tag_mi = " . (int)$_POST['tag_mi'] . ",
					tag_do = " . (int)$_POST['tag_do'] . ",
					tag_fr = " . (int)$_POST['tag_fr'] . ",
					tag_sa = " . (int)$_POST['tag_sa'] . ",
					tag_so = " . (int)$_POST['tag_so'] . "
					WHERE asvid='" . $_POST['asvid'] . "'");

			} else {

				DB::getDB()->query("INSERT INTO ganztags_schueler (`asvid`,`info`,`gruppe`,`tag_mo`,`tag_di`,`tag_mi`,`tag_do`,`tag_fr`,`tag_sa`,`tag_so`)
				values (
					'" . DB::getDB()->escapeString($_POST['asvid']) . "',
					'" . DB::getDB()->escapeString($_POST['info']) . "',
					'" . DB::getDB()->escapeString($_POST['gruppe']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_mo']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_di']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_mi']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_do']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_fr']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_sa']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_so']) . "'
				) ");

			}
			
			header("Location: index.php?page=ganztags");
			exit(0);
					
		}


		if(!$_REQUEST['id']) {
			DB::showError("Diese Seite ist leider für Sie nicht sichtbar.");
			die();
		}
		$asvid = $_REQUEST['id'];

		$schueler_query = DB::getDB()->query("SELECT * FROM ganztags_schueler WHERE asvid = '".DB::getDB()->escapeString($_REQUEST["id"])."' ");
		$schueler = false;
		while($row = mysqli_fetch_array($schueler_query)) { $schueler = $row; }

		$schueler_info = $schueler['info'];

		$checked_tag_mo = '';
		if ($schueler['tag_mo']) { $checked_tag_mo = 'checked="checked"'; }

		$checked_tag_di = '';
		if ($schueler['tag_di']) { $checked_tag_di = 'checked="checked"'; }

		$checked_tag_mi = '';
		if ($schueler['tag_mi']) { $checked_tag_mi = 'checked="checked"'; }

		$checked_tag_do = '';
		if ($schueler['tag_do']) { $checked_tag_do = 'checked="checked"'; }

		$checked_tag_fr = '';
		if ($schueler['tag_fr']) { $checked_tag_fr = 'checked="checked"'; }

		$checked_tag_sa = '';
		if ($schueler['tag_sa']) { $checked_tag_sa = 'checked="checked"'; }

		$checked_tag_so = '';
		if ($schueler['tag_so']) { $checked_tag_so = 'checked="checked"'; }
		

		$gruppen_query = DB::getDB()->query("SELECT * FROM ganztags_gruppen ORDER BY sortOrder ");
		$select_gruppe = '<select name="gruppe">';
		$select_gruppe .= '<option value=""> - </option>';
		while($row = mysqli_fetch_array($gruppen_query)) {
			$selected = '';
			if($row['id'] == $schueler['gruppe']) {
				$selected = 'selected="selected"';
			}
			$select_gruppe .= '<option value="'.$row['id'].'" '.$selected.'>'.$row['name'].'</option>';
		}
		$select_gruppe .= '<select>';

		
		eval("echo(\"" . DB::getTPL()->get("ganztags/edit"). "\");");
		
	}
	
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getSiteDisplayName() {
		return "Ganztags Bearbeiten";
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
	


}


?>