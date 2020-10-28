<?php


class dokumente extends AbstractPage {

	private $isAdmin = false;
	
	public function __construct() {
		parent::__construct(array("Dokumente und Formulare"));
		
		$this->checkLogin();
	}

	public function execute() {
		
		if($_REQUEST['action'] == 'downloadFile') {
			$file = dokumenteDokument::getByID(intval($_REQUEST['fileID']));
			if($file != null) {
				$sectionID = $file->getBereich()->getSectionID();
				$section = dokumenteKategorie::getByID($sectionID);
				
				if($section->hasAccess()) {
					$file->sendFile();
				}
				else {
					new errorPage();
				}
			}
			else new errorPage();
		}
		
		$section = dokumenteKategorie::getByID(intval($_REQUEST['sectionID']));
		
		if($section == null) new errorPage();
		
		if(!$section->hasAccess()) {
			new errorPage();
		}
		
		parent::__construct(array("Dokumente und Formulare", $section->getName()));
		
		$gruppen = $section->getGruppen();
				
		$htmlGruppen = "";
		
		for($i = 0; $i < sizeof($gruppen); $i++) {
			$files = $gruppen[$i]->getFiles(false);
			
			$fileHTML = "";
			for($f = 0; $f < sizeof($files); $f++) {
				$fileHTML .= "<tr><td><b><a href=\"index.php?page=dokumente&action=downloadFile&fileID=" . $files[$f]->getID() . "\">" . $files[$f]->getName() . "</a></b>" . (($files[$f]->getKommentar() != "") ? ("<br /><small>" . $files[$f]->getKommentar() . "</small>") : ("")) . "</b>";
				$fileHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($files[$f]->getDate()) . "</td>";
				
				$fileHTML .= "<td>" . $files[$f]->getDownloads() . " Downloads<br />" . $files[$f]->getSizeInMB() . " MB</td>";
				
				$fileHTML .= "<td><form><button type=\"button\" class=\"btn btn-success\" onclick=\"window.location.href='index.php?page=dokumente&action=downloadFile&fileID=" . $files[$f]->getID() . "'\"><i class=\"fa fa-download\"></i></form></td>";
			
				$fileHTML .= "</tr>";
			}
			
			eval("\$htmlGruppen .= \"" . DB::getTPL()->get("dokumente/gruppe") . "\";");
		}
		
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("dokumente/index") . "\");");
	}
	
	
	public static function hasSettings() {
		return false;
	}
	
	/**
	 * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
	 * @return array(String, String)
	 * array(
	 * 	   array(
	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
	 *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
	 *      'titel' => "Titel der Beschreibung",
	 *      'text' => "Text der Beschreibung"
	 *     )
	 *     ,
	 *     .
	 *     .
	 *     .
	 *  )
	 */
	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Dokumente und Formulare';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Elternbriefe';
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-file';
	}
	
	public static function getAdminMenuGroup() {
		return 'Dokumente und Formulare';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-file';
	}
	
	public static function displayAdministration($selfURL) {
		switch($_REQUEST['action']) {
			case 'addUserAllAccess':
				$group = usergroup::getGroupByName('Webportal_Dokumente_All_Accesss');
				$group->addUser(intval($_POST['userID']));
				header("Location: $selfURL");
				exit(0);
				
			case 'RemoveUserAllAccess':
				$group = usergroup::getGroupByName('Webportal_Dokumente_All_Accesss');
				$group->removeUser(intval($_GET['userID']));
				header("Location: $selfURL");
				exit(0);
				

			
			case 'addLehrerAccess':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					$section->setAccessLehrer(true);
				}
				header("Location: $selfURL");
				exit(0);
				
			case 'removeLehrerAccess':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					$section->setAccessLehrer(false);
				}
				header("Location: $selfURL");
				exit(0);

			case 'addSchuelerAccess':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					$section->setAccessSchueler(true);
				}
				header("Location: $selfURL");
				exit(0);
				
			case 'removeSchuelerAccess':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					$section->setAccessSchueler(false);
				}
				header("Location: $selfURL");
				exit(0);
				
			case 'addElternAccess':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					$section->setAccessEltern(true);
				}
				header("Location: $selfURL");
				exit(0);
				
			case 'removeElternAccess':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					$section->setAccessEltern(false);
				}
				header("Location: $selfURL");
				exit(0);

			
			case 'removeSection':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					$section->delete();
				}
				header("Location: $selfURL");
				exit(0);
		
			case 'renameSection':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					$section->rename(DB::getDB()->escapeString($_POST['sectionName']));
				}
				header("Location: $selfURL");
				exit(0);
				
			case 'manageFiles':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					return self::manageFiles($selfURL, $section);
				}
				header("Location: $selfURL");
				exit(0);
				
			case 'addSection':
				DB::getDB()->query("INSERT INTO dokumente_kategorien
						(
							kategorieName,
							kategorieAccessLehrer,
							kategorieAccessSchueler,
							kategorieAccessEltern
						) VALUES (
							'" . DB::getDB()->escapeString($_POST['newSectionName']) . "',
							" . (($_POST['accessTeacher'] == "1") ? 1 : 0) . ",
							" . (($_POST['accessSchueler'] == "1") ? 1 : 0) . ",
							" . (($_POST['accessEltern'] == "1") ? 1 : 0) . "
						)
						");
				header("Location: $selfURL");
				exit(0);
				
			case 'addbereich':
				$section = dokumenteKategorie::getByID(intval($_GET['sectionID']));
				if($section != null) {
					
					DB::getDB()->query("INSERT INTO dokumente_gruppen
							(
							gruppenName,
							kategorieID)
							VALUES (
							'" . DB::getDB()->escapeString($_POST['newBereichName']) . "',
							'" . $section->getID() . "')");
					header("Location: $selfURL&action=manageFiles&sectionID=" . $section->getID());
					exit(0);
				}
				else {
					header("Location: $selfURL");
					exit(0);
				}
			break;
			
			case 'uploadFile':
				$bereich = dokumenteGruppe::getByID(intval($_REQUEST['bereichID']));
				
				if($bereich != null) {
					$bereich->uploadFile();
				}
				
				header("Location: $selfURL&action=manageFiles&sectionID=" . $bereich->getSectionID());
				exit(0);
			break;
			
			case 'deleteFile':
				$file = dokumenteDokument::getByID(intval($_REQUEST['fileID']));
				
				
				$idBereich = -1;
				
				if($file != null) {
					$bereich = $file->getBereich();
					if($bereich != null) $idBereich = $bereich->getSectionID();
					
					$file->delete();
				}
				
				if($idBereich > 0) header("Location: $selfURL&action=manageFiles&sectionID=" . $idBereich);
				else header("Location: $selfURL");
				exit(0);
			break;
			
			case 'moveInOtherGroup':
				$file = dokumenteDokument::getByID(intval($_REQUEST['fileID']));
				
				$gruppe = dokumenteGruppe::getByID(intval($_REQUEST['gruppenID']));

				$idBereich = -1;
				
				if($file != null && $gruppe != null) {
					$bereich = $file->getBereich();
					if($bereich != null) $idBereich = $bereich->getSectionID();
					
					$file->changeGroup($gruppe->getID());
				}
				
				if($idBereich > 0) header("Location: $selfURL&action=manageFiles&sectionID=" . $idBereich);
				else header("Location: $selfURL");
				exit(0);
			break;
			
			case 'getFile':
				$file = dokumenteDokument::getByID(intval($_REQUEST['fileID']));
								
				if($file != null) {
					$file->sendFile();
				}
				
				new errorPage();
			
			case 'deleteBereich':
				$bereich = dokumenteGruppe::getByID(intval($_REQUEST['bereichID']));
				
				if($bereich != null) {
					$bereich->delete();
				}
				
				header("Location: $selfURL&action=manageFiles&sectionID=" . $bereich->getSectionID());
			break;
			
			
			case 'editBereich':
				$bereich = dokumenteGruppe::getByID(intval($_REQUEST['bereichID']));
			
				if($bereich != null) {
					$bereich->updateName($_REQUEST['bereichName']);
				}
			
				header("Location: $selfURL&action=manageFiles&sectionID=" . $bereich->getSectionID());
				break;
					
			default:
				return self::displayAdminIndex($selfURL);
			break;
		}
	}
	
	/**
	 * 
	 * @param dokumenteKategorie $section
	 */
	private static function manageFiles($selfURL, $section) {
		$gruppen = $section->getGruppen();
		
		
		$displayHTML = "";
		
		
		$submitButtonsOtherSections = "";
		
		for($i = 0; $i < sizeof($gruppen); $i++) {
			
			$submitButtonsOtherSections .= "<p><button type=\"submit\" name=\"gruppenID\" class=\"btn btn-default btn-block\" value=\"" . $gruppen[$i]->getID() . "\"><i class=\"fa fa-arrow-right\"></i> Nach \"" . $gruppen[$i]->getName() . "\" verschieben</button></p>";
			
		}
		
		for($i = 0; $i < sizeof($gruppen); $i++) {
			$dateien = $gruppen[$i]->getFiles();
			
			$fileListe = "";
			
			
			for($f = 0; $f < sizeof($dateien); $f++) {
				eval("\$fileListe .= \"" . DB::getTPL()->get("dokumente/admin/file") . "\";");
			}
			
			eval("\$displayHTML .= \"" . DB::getTPL()->get("dokumente/admin/gruppe") . "\";");
			
		}
		
		
		
		$html = "";
		
		eval("\$html .= \"" . DB::getTPL()->get("dokumente/admin/sectionfiles") . "\";");
		
		return $html;
	}
	
	private static function displayAdminIndex($selfURL) {
		$html = "";
		
		if(!is_writable("../data/dokumente/")) {
			$html = "<div class=\"callout callout-danger\"><i class=\"fa fa-file\"></i> Das Modul steht leider nicht zur Verfügung, da kein Verzeichnis zur Speicherung der Dokumente existiert!</div>";
			return $html;
		}
		
		$accessAllSections = administrationmodule::getUserListWithAddFunction(
				$selfURL,
				"alldocumentsaccess",
				"addUserAllAccess",
				"RemoveUserAllAccess",
				"Benutzer mit Zugriff auf alle Bereiche",
				"Benutzer, die hier aufgeführt sind, haben Zugriff auf alle Bereiche, auch wenn sie nicht Lehrer, Schüler oder Eltern sind. Das ist z.B. gedacht für Sekretariatskräfte.",
				"Webportal_Dokumente_All_Accesss"
		);
		
		$sectionHTML = "";
		
		$sections = dokumenteKategorie::getAll();
		for($i = 0; $i < sizeof($sections); $i++) {
			eval("\$sectionHTML .= \"" . DB::getTPL()->get("dokumente/admin/bit") . "\";");
		}
		
		eval("\$html = \"" . DB::getTPL()->get("dokumente/admin/index") . "\";");
		
		return $html;
	}
}


?>