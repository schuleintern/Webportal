<?php


class ganztagsEdit extends AbstractPage {
	

	public function __construct() {
		
		parent::__construct(array("Lehrertools", "Ganztags Bearbeiten"));
				
		
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
					tag_so = " . (int)$_POST['tag_so'] . ",
					tag_mo_info = '" . DB::getDB()->escapeString($_POST['tag_mo_info']) . "',
					tag_di_info = '" . DB::getDB()->escapeString($_POST['tag_di_info']) . "',
					tag_mi_info = '" . DB::getDB()->escapeString($_POST['tag_mi_info']) . "',
					tag_do_info = '" . DB::getDB()->escapeString($_POST['tag_do_info']) . "',
					tag_fr_info = '" . DB::getDB()->escapeString($_POST['tag_fr_info']) . "',
					tag_sa_info = '" . DB::getDB()->escapeString($_POST['tag_sa_info']) . "',
					tag_so_info = '" . DB::getDB()->escapeString($_POST['tag_so_info']) . "'
					WHERE asvid='" . $_POST['asvid'] . "'");

			} else {

				DB::getDB()->query("INSERT INTO ganztags_schueler (`asvid`,`info`,`gruppe`,`tag_mo`,`tag_di`,`tag_mi`,`tag_do`,`tag_fr`,`tag_sa`,`tag_so`
                                ,`tag_mo_info`,`tag_di_info`,`tag_mi_info`,`tag_do_info`,`tag_fr_info`,`tag_sa_info`,`tag_so_info`)
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
					'" . DB::getDB()->escapeString($_POST['tag_so']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_mo_info']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_di_info']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_mi_info']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_do_info']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_fr_info']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_sa_info']) . "',
					'" . DB::getDB()->escapeString($_POST['tag_so_info']) . "'
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
		$ganztags = false;
		while($row = mysqli_fetch_array($schueler_query)) { $ganztags = $row; }

		$schueler_info = str_replace('"',"'", $ganztags['info']);

        $tag_mo_info = str_replace('"',"'", $ganztags['tag_mo_info']);
        $tag_di_info = str_replace('"',"'", $ganztags['tag_di_info']);
        $tag_mi_info = str_replace('"',"'", $ganztags['tag_mi_info']);
        $tag_do_info = str_replace('"',"'", $ganztags['tag_do_info']);
        $tag_fr_info = str_replace('"',"'", $ganztags['tag_fr_info']);
        $tag_sa_info = str_replace('"',"'", $ganztags['tag_sa_info']);
        $tag_so_info = str_replace('"',"'", $ganztags['tag_so_info']);
		
		$schueler = schueler::getByAsvID($asvid);

		$schueler_name = $schueler->getCompleteSchuelerName();

		$checked_tag_mo = '';
		if ($ganztags['tag_mo']) { $checked_tag_mo = 'checked="checked"'; }

		$checked_tag_di = '';
		if ($ganztags['tag_di']) { $checked_tag_di = 'checked="checked"'; }

		$checked_tag_mi = '';
		if ($ganztags['tag_mi']) { $checked_tag_mi = 'checked="checked"'; }

		$checked_tag_do = '';
		if ($ganztags['tag_do']) { $checked_tag_do = 'checked="checked"'; }

		$checked_tag_fr = '';
		if ($ganztags['tag_fr']) { $checked_tag_fr = 'checked="checked"'; }

		$checked_tag_sa = '';
		if ($ganztags['tag_sa']) { $checked_tag_sa = 'checked="checked"'; }

		$checked_tag_so = '';
		if ($ganztags['tag_so']) { $checked_tag_so = 'checked="checked"'; }
		

		$gruppen_query = DB::getDB()->query("SELECT * FROM ganztags_gruppen ORDER BY sortOrder ");
		$select_gruppe = '<select name="gruppe">';
		$select_gruppe .= '<option value=""> - </option>';
		while($row = mysqli_fetch_array($gruppen_query)) {
			$selected = '';
			if($row['id'] == $ganztags['gruppe']) {
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