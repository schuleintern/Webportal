<?php


/**
 * Projektverwaltung 9. Klasse
 * // TODO: Remake mit ASV Datensätzen und Drucken der Zertifikate?
 * @author Christian Spitschka
 * @deprecated
 */
class projektverwaltung extends AbstractPage {

	private $info;
	
	public function __construct() {
		$this->needLicense = true;
		
		parent::__construct(array("Lehrertools", "Projektverwaltung"));
		
		$this->checkLogin();
		
		if(!DB::getSession()->isTeacher()) {
			header("Location: index.php");
			exit(0);
		}
		
		if($_GET['action'] == "admin") {
		    if(!DB::getSession()->isAdmin()) $this->checkAccessWithGroup("Webportal_Projektverwaltung_Admin");
		}
	}

	public function execute() {		
		if($_GET['action'] == "admin") {
			$this->adminPage();
			exit(0);
		}
		elseif($_GET['action'] == "grade") {
			if($_GET['mode'] == "print") $this->showPrintVersion($_GET['gradeName']);
			if($_GET['mode'] == "print2") $this->showPrintVersion2($_GET['gradeName']);
			else $this->showGrade($_GET['gradeName']);
		}
		else {
			die("Error 2983471298ddwww3e");
		}
	}
	
	private function showGrade($grade) {
		if($this->checkGradeAccess($grade)) {
			
			if($_GET['mode'] == "save") {
				$dssave = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerKlasse='" . $grade . "' ORDER BY schuelerName ASC, schuelerRufname ASC");	
				while($schueler = DB::getDB()->fetch_array($dssave)) {
					$schueler['userID'] = $schueler['schuelerAsvID'];
					$userid = $schueler['userID'];
					
					
				
					DB::getDB()->query("INSERT INTO projekt_projekte 
							(userID, projektName, projektErfolg, projektFach1, projektFach2, projektLehrer1, projektLehrer2)
							values(
								'$userid',					
								'" . addslashes($_POST['thema_' . $userid]) . "',
								'" . addslashes($_POST['erfolg_' . $userid]) . "',
								'" . addslashes($_POST['fach1_' . $userid]) . "',
								'" . addslashes($_POST['fach2_' . $userid]) . "',
								'" . addslashes($_POST['lehrer1_' . $userid]) . "',
								'" . addslashes($_POST['lehrer2_' . $userid]) . "'
							) 
							ON DUPLICATE KEY UPDATE				
								projektName='" . addslashes($_POST['thema_' . $userid]) . "',
								projektErfolg='" . addslashes($_POST['erfolg_' . $userid]) . "',
								projektFach1='" . addslashes($_POST['fach1_' . $userid]) . "',
								projektFach2='" . addslashes($_POST['fach2_' . $userid]) . "',
								projektLehrer1='" . addslashes($_POST['lehrer1_' . $userid]) . "',
								projektLehrer2='" . addslashes($_POST['lehrer2_' . $userid]) . "'
							");
				}
			}
			
			// Schueler der Klasse
			$schueler = array();
			
			$ds = DB::getDB()->query("SELECT * FROM schueler LEFT JOIN projekt_projekte ON userID=schuelerASVID WHERE schuelerKlasse='" . $grade . "' ORDER BY schuelerName ASC, schuelerRufname ASC");	
			$tablecontent = "";
			
			$selected = array();
			

			
			$nr = 0;
			while($schueler = DB::getDB()->fetch_array($ds)) {
				$nr++;
				
				$selected[0] = "";
				$selected[1] = "";
				$selected[2] = "";
				
				$schueler['userID'] = $schueler['schuelerAsvID'];
				
				if($schueler['projektErfolg'] == "sehr guter Erfolg") {
					$selected[0] = " selected=\"selected\"";
				}
				
				if($schueler['projektErfolg'] == "guter Erfolg") {
					$selected[1] = " selected=\"selected\"";
				}
				
				if($schueler['projektErfolg'] == "Erfolg") {
					$selected[2] = " selected=\"selected\"";
				}
				
				eval("\$tablecontent .=\"" . DB::getTPL()->get("projektverwaltung/viewGradeBit") . "\";");
			}
			
			eval("echo(\"" . DB::getTPL()->get("projektverwaltung/viewGrade") . "\");");
			
		}
		else {
			echo("Leider keinen Zugriff auf diese Klasse.");
		}
	}
	
	private function showPrintVersion($grade) {
		if($this->checkGradeAccess($grade)) {
				
			// Schueler der Klasse
			$schueler = array();
				
			$ds = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerKlasse='" . $grade . "' ORDER BY schuelerName ASC, schuelerRufname ASC");	
			$tablecontent = "";
				
			$nr = 0;
			while($schueler = DB::getDB()->fetch_array($ds)) {
				$nr++;
				
				$schueler['userID'] = $schueler['schuelerAsvID'];
		
				eval("\$tablecontent .=\"" . DB::getTPL()->get("projektverwaltung/viewGradeBitPrint") . "\";");
			}
			
			eval("\$print = \"" . DB::getTPL()->get("projektverwaltung/viewGradePrint") . "\";");
			
			$print = ($print);
			
			$mpdf=new mPDF('utf-8', 'A4-L');
			$mpdf->Bookmark("Projektliste");
			$mpdf->WriteHTML($print);
			
			$mpdf->Output();
			
			// 
			exit();
			
				
		}
		else {
			echo("Leider keinen Zugriff auf diese Klasse.");
		}
	}
	
	private function showPrintVersion2($grade) {
		if($this->checkGradeAccess($grade)) {
	
			// Schueler der Klasse
			$schueler = array();
	
			$ds = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerKlasse='" . $grade . "' ORDER BY schuelerName ASC, schuelerRufname ASC");	
			$tablecontent = "";
	
			$nr = 0;
			while($schueler = DB::getDB()->fetch_array($ds)) {
				$nr++;
	
				$schueler['userID'] = $schueler['schuelerAsvID'];
				
				
				eval("\$tablecontent .=\"" . DB::getTPL()->get("projektverwaltung/viewGradeBitPrint2") . "\";");
			}
			
			eval("\$print = \"" . DB::getTPL()->get("projektverwaltung/viewGradePrint2") . "\";");
				
			$print = ($print);
				
			$mpdf=new mPDF('utf-8', 'A4-L');
			$mpdf->Bookmark("Projektliste");
			$mpdf->WriteHTML($print);
				
			$mpdf->Output();
			exit();
	
		}
		else {
			echo("Leider keinen Zugriff auf diese Klasse.");
		}
	}
	
	private function checkGradeAccess($grade) {
		// Meine Klassen anzeigen

		$meineKlassen = array();
		
		if(in_array("Webportal_Projektverwaltung_Admin", DB::getSession()->getGroupNames())) {
			$meineKlassen = grade::getAllGradesAtLevel(9);
		}
		else {
			$grs = DB::getDB()->query("SELECT * FROM projekt_lehrer2grade WHERE lehrerUserID='" . DB::getSession()->getUserID() . "'");

			while($g = DB::getDB()->fetch_array($grs)) {
				$meineKlassen[] = $g['gradeName'];
			}
		}
		
		if(sizeof($meineKlassen) == 0) {
			return false;
		}
		else return in_array($grade, $meineKlassen);
		
	}
	
	private function adminPage() {
		$neunteKlassen = grade::getAllGradesAtLevel(9);
		
		if($_GET['mode'] == "print") {
			$colLetters = array (
					"A",
					"B",
					"C",
					"D",
					"E",
					"F",
					"G",
					"H",
					"I",
					"J",
					"K",
					"L",
					"M",
					"N",
					"O",
					"P",
					"Q",
					"R",
					"S",
					"T",
					"U",
					"V",
					"W",
					"X",
					"Y",
					"Z",
					"AA",
					"AB",
					"AC",
					"AD",
					"AE"
			);
			
			include_once("../framework/lib/phpexcel/PHPExcel.php");
			
			$allData = array();
			
			for($i = 0; $i < sizeof($neunteKlassen); $i++) {
				$data = DB::getDB()->query("SELECT * FROM projekt_projekte, schueler WHERE schueler.schuelerASVID=projekt_projekte.userID AND schuelerKlasse='" . $neunteKlassen[$i] . "' ORDER BY schuelerName ASC, schuelerRufname ASC");
				while($item = DB::getDB()->fetch_array($data)) {
					$allData[] = array(
							"Vorname" => $item['schuelerRufname'],
							"Nachname" => $item['schuelerName'],
							"Klasse" => $neunteKlassen[$i],
							"Thema" => $item['projektName'],
							"Erfolg" => $item['projektErfolg'],
							"Fach1" => $item['projektFach1'],
							"Fach2" => $item['projektFach2'],
							"Lehrer1" => $item['projektLehrer1'],
							"Lehrer2" => $item['projektLehrer2']							
					);
				}
			}

            include_once('../framework/lib/phpexcel/PHPExcel.php');

			$excelFile = new PHPExcel();
			// Set document properties
			
			$excelFile->getProperties()
			->setCreator(DB::getGlobalSettings()->siteNamePlain)
			->setTitle('Projekte')
			->setLastModifiedBy('RSU Intern')
			->setDescription('');
			
			$excelFile->setActiveSheetIndex(0)->setCellValue("A1","Name");
			$excelFile->setActiveSheetIndex(0)->setCellValue("B1","Vorname");
			$excelFile->setActiveSheetIndex(0)->setCellValue("C1","Klasse");
			$excelFile->setActiveSheetIndex(0)->setCellValue("D1","Thema");
			$excelFile->setActiveSheetIndex(0)->setCellValue("E1","Erfolg");
			$excelFile->setActiveSheetIndex(0)->setCellValue("F1","Fach1");
			$excelFile->setActiveSheetIndex(0)->setCellValue("G1","Fach2");
			$excelFile->setActiveSheetIndex(0)->setCellValue("H1","Lehrer1");
			$excelFile->setActiveSheetIndex(0)->setCellValue("I1","Lehrer2");
			
			for($i = 0; $i < sizeof($allData); $i++) {
				$row = $i+2;
				
				if($allData[$i]['Erfolg'] == "sehr guter Erfolg") $allData[$i]['Erfolg'] = "mit sehr gutem Erfolg";
				if($allData[$i]['Erfolg'] == "guter Erfolg") $allData[$i]['Erfolg'] = "mit gutem Erfolg";
				if($allData[$i]['Erfolg'] == "Erfolg") $allData[$i]['Erfolg'] = "mit Erfolg";
				if($allData[$i]['Erfolg'] == "") $allData[$i]['Erfolg'] = "";
				
				$excelFile->setActiveSheetIndex(0)->setCellValue("A$row",$allData[$i]['Nachname']);
				$excelFile->setActiveSheetIndex(0)->setCellValue("B$row",$allData[$i]['Vorname']);
				$excelFile->setActiveSheetIndex(0)->setCellValue("C$row",$allData[$i]['Klasse']);
				$excelFile->setActiveSheetIndex(0)->setCellValue("D$row",str_replace("`","'",str_replace("´","'",$allData[$i]['Thema'])));
				$excelFile->setActiveSheetIndex(0)->setCellValue("E$row",$allData[$i]['Erfolg']);
				$excelFile->setActiveSheetIndex(0)->setCellValue("F$row",$allData[$i]['Fach1']);
				$excelFile->setActiveSheetIndex(0)->setCellValue("G$row",$allData[$i]['Fach2']);
				$excelFile->setActiveSheetIndex(0)->setCellValue("H$row",$allData[$i]['Lehrer1']);
				$excelFile->setActiveSheetIndex(0)->setCellValue("I$row",$allData[$i]['Lehrer2']);
				
			}
			
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="Projekte_9.Klassen.xlsx"');
			header('Cache-Control: max-age=0');
				// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');
				// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0
			
			$objWriter = PHPExcel_IOFactory::createWriter($excelFile, 'Xlsx');
			$objWriter->save('php://output');
			
			exit(0);
		}
		
		
		$gradeList = implode(", ", $neunteKlassen);
		
		$selectListTeacher = "";
		
		$teacher = DB::getDB()->query("SELECT userID, userName, userFirstName, userLastName FROM users WHERE userID IN (SELECT lehrerUserID FROM lehrer WHERE lehrerUserID > 0) ORDER BY userLastName, userFirstName");
		while($t = DB::getDB()->fetch_array($teacher)) {
			$selectListTeacher .= "<option value=\"{$t['userID']}\">{$t['userLastName']}, {$t['userFirstName']} ({$t['userName']})</option>\n";
		}
		
		
		$gradeListAdmin = "";
		
		for($i = 0; $i < sizeof($neunteKlassen); $i++) {
			if($_GET['mode'] == "addTeacher" && $_GET['gradeName'] == $neunteKlassen[$i]) {
				
				$userID = intval($_POST['teacherUserID']);
				
				DB::getDB()->query("INSERT INTO projekt_lehrer2grade (lehrerUserID, gradeName) values('" . $userID . "','" . $neunteKlassen[$i] . "') ON DUPLICATE KEY UPDATE lehrerUserID=lehrerUserID");
			}
			
			if($_GET['mode'] == "deleteTeacher" && $_GET['gradeName'] == $neunteKlassen[$i]) {
				$userID = intval($_GET['deleteUserID']);
			
				DB::getDB()->query("DELETE FROM projekt_lehrer2grade WHERE gradeName='" . $neunteKlassen[$i] . "' AND lehrerUserID='" . $userID . "'");
			}
			
			$teacherList = "";
			// Momentane Lehrer
			$teachers = DB::getDB()->query("SELECT userID, userName, userFirstName, userLastName FROM projekt_lehrer2grade JOIN users ON projekt_lehrer2grade.lehrerUserID=users.userID WHERE gradeName='" . $neunteKlassen[$i] . "'");
			while($t = DB::getDB()->fetch_array($teachers)) {
				$teacherList .= "<li>{$t['userLastName']},  {$t['userFirstName']} ({$t['userName']}) (<a href=\"index.php?page=projektverwaltung&action=admin&mode=deleteTeacher&gradeName={$neunteKlassen[$i]}&deleteUserID={$t['userID']}\">Löschen</a>)";
			}
			
			eval("\$gradeListAdmin .= \"" . DB::getTPL()->get("projektverwaltung/admin/gradeBit") . "\";");
		}
		
		
		eval("echo(\"" . DB::getTPL()->get("projektverwaltung/admin/index") . "\");");
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
		return 'Projektverwaltung für 9. Klasse';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array(
				array(
						"groupName" => "Webportal_Projektverwaltung_Admin",
						"beschreibung" => "Administrator der Projektverwaltung"
				)
			);
	
	}
}


?>