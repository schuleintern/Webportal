<?php


class klassenlisten extends AbstractPage {
	
	private $isAdmin = false;
	private $isTeacher = false;
	
	private $colLetters = array (
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

	public function __construct() {
		
		parent::__construct(array("Lehrertools", "Klassenlisten"));
				
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
		
		//include_once("../framework/lib/phpexcel/PHPExcel.php");
				
		$today = date("d.m.Y");
		
		if(!$this->isTeacher) {
			DB::showError("Diese Seite ist leider für Sie nicht sichtbar.");
			die();
		}
		
		if(isset($_REQUEST['grade']) && $_REQUEST['grade'] != "") {
			

				$klasse = klasse::getByName($_REQUEST['grade']);
				
				if($klasse != null) {
					$pupils = $klasse->getSchueler($_REQUEST['withAusgetretene'] > 0);
				}
				
				
				if(isset($_REQUEST['createPDF']) && $_REQUEST['createPDF'] != "") {
					
					$cols = $_REQUEST['spalten'];
					
					
					$colPrint = array();
					for($i = 0; ($i < sizeof($cols) && $i < 10); $i++) {
						if(trim($cols[$i]) != "") {
							$colPrint[] = (trim($cols[$i]));
						}
					}
					
									
					$printContent = "<table border=\"1\" cellpadding=\"3\" width=\"100%\">";
					$colRowDataBlank = "";
					
					$abzug = 35;
					if($_REQUEST['gebdatum'] > 0) {
						$abzug += 15;
					}
					
					if(sizeof($colPrint) > 0) $perCel = floor((100-$abzug)/sizeof($colPrint));	// Breite der einzelnen Zellen
					else {
						$colPrint[] = "&nbsp;";
						$perCel = 100 - $abzug;
					}
					
					$printContent .= "<tr><th width=\"5%\" align=\"center\">#</th><th width=\"30%\"><b>" . $this->getTableTHForName() . "</b></th>";
					
					if($_REQUEST['gebdatum'] == 1) {
						$printContent .= "<td width=\"15%\"><font size=\"-2\"><b>Geburtsdatum</b></font></td>";
					}
										
					for($i = 0; $i < sizeof($colPrint); $i++) {
						$printContent .= "<td width=\"$perCel%\"><b>" . $colPrint[$i] . "</b></td>";
						$colRowDataBlank .= "<td>&nbsp;</td>";
					}
					$printContent .= "</tr>";
					
					for($i = 0; $i < sizeof($pupils); $i++) {
						$printContent .= "<tr>";
						
						$printContent .= "<td align=\"center\">" . ($i+1) . "</td>";
						
						$printContent .= "<td>" . $this->getName($pupils[$i]);
						
						if($pupils[$i]->isAusgetreten()) {
							$printContent .= "<br /><i>A: " . DateFunctions::getNaturalDateFromMySQLDate($pupils[$i]->getAustrittDatumAsMySQLDate()) . "</i>";
						}
						
						$printContent .= "</td>";
						
						if($_REQUEST['gebdatum'] > 0) {
							$printContent .= "<td>" . $pupils[$i]->getGeburtstagAsNaturalDate() . "</td>";
						}
						
						$printContent .= $colRowDataBlank;
						
						$printContent .= "</tr>\n";
					}
					
					$printContent .= "</table>";
					$grade = $_REQUEST['grade'];
					
					$kNamen = [];
					
					$kls = $klasse->getKlassenLeitung();
					for($k = 0; $k < sizeof($kls); $k++) {
						$kNamen[] =  $kls[$k]->getDisplayNameMitAmtsbezeichnung();
					}
					
					$klassenleitung = "Klassenleitung: " . implode(" | ", $kNamen);
					
										
					eval("\$print .= \"" . DB::getTPL()->get("klassenlisten/pdf/index") . "\";");
					
					//echo $print;
					//die();


                    $format = 'P';
                    if ($_REQUEST['format'] == 'a4q') {
                        //querformat
                        $format = 'L';
                    }

					$pdfPage = new PrintNormalPageA4WithHeader("Klassenliste ".$klasse->getKlassenName(), 'A4', $format );
					$pdfPage->setHTMLContent($print);
					$pdfPage->showStand();
					$pdfPage->send();
					
					// echo($print);
					// die();
					/*				
					$print = ($print);
					
					if($_POST['format'] == "a4q") $format = "A4-L";
					else $format = "A4-P";
					
					$mpdf = new mPDF('utf-8', $format);
					$mpdf->Bookmark("Klassenkalender");
					$mpdf->setFooter('Stand: ' . $today);
					$mpdf->WriteHTML($print);
					
					$mpdf->Output("Klassenliste_" . $grade . ".pdf","D");
					exit(); */
					
				}
				elseif(isset($_REQUEST['createXLSX']) && $_REQUEST['createXLSX'] != "") {
					error_reporting(0);
					// Excel
					$grade = $_REQUEST['grade'];
					
					$excelFile = new PHPExcel();
					// Set document properties
					
					$excelFile->getProperties()
						->setCreator(DB::getGlobalSettings()->siteNamePlain)
						->setTitle('Klassenliste ' . $grade)
						->setLastModifiedBy(DB::getGlobalSettings()->siteNamePlain)
						->setDescription('Klassenliste der Klasse ' . $grade . ' mit Korrekturschema.');
					
					// Add some data
					$excelFile->setActiveSheetIndex(0)->setCellValue("A1","Name");
					$excelFile->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
					
					$aufgaben = intval($_POST['aufgabenAnzahl']);
					if($aufgaben > 0) {
						for($i = 1; $i <= $aufgaben && $i <= 20; $i++) {
							$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$i] . "1","Aufgabe " . $i);
							$excelFile->getActiveSheet()->getStyle($this->colLetters[$i] . "1")->getFont()->setBold(true);
							
							$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$i] . (sizeof($pupils)+3),"5");
						}
					}
					
					
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+1] . "1","Gesamtpunkte");
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+2] . "1","Note");
					
					$excelFile->getActiveSheet()->getStyle($this->colLetters[$aufgaben+1] . "1")->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle($this->colLetters[$aufgaben+2] . "1")->getFont()->setBold(true);
					
					$lastCol = $this->colLetters[$aufgaben+2];
					$freeCol = $this->colLetters[$aufgaben+3];
					
					$oben = array();
					$unten = array();
					
					// Notenschlüssel erzeugen
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+4] . "1","Note");
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+5] . "1","Start");
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+6] . "1","Ende");
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+7] . "1","Breite");
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+8] . "1","Anzahl");
					
					$excelFile->getActiveSheet()->getStyle($this->colLetters[$aufgaben+4] . "1")->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle($this->colLetters[$aufgaben+5] . "1")->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle($this->colLetters[$aufgaben+6] . "1")->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle($this->colLetters[$aufgaben+7] . "1")->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle($this->colLetters[$aufgaben+8] . "1")->getFont()->setBold(true);
					
					for($i = 1; $i <= 6; $i++) {
						$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+4] . ($i+1),$i);
						$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+5] . ($i+1),(($i == 1) ? ("=" . $this->colLetters[$aufgaben+1] . (sizeof($pupils)+3)) : ("=" . $this->colLetters[$aufgaben+6] . ($i) . "-0.5")));
						$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+6] . ($i+1),"=" . $this->colLetters[$aufgaben+5] . ($i+1) . "-" . $this->colLetters[$aufgaben+7] . ($i+1));
						$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+7] . ($i+1),floor(($aufgaben*5)/6));
						$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+8] . ($i+1),"=COUNTIF(" . $this->colLetters[$aufgaben+2] . "2:" . $this->colLetters[$aufgaben+2] . (sizeof($pupils)+1) . "," . $i . ")");
							
						$oben[$i] = $this->colLetters[$aufgaben+5] . ($i+1);
						$unten[$i] = $this->colLetters[$aufgaben+6] . ($i+1);
						
						
					}
					
					// /Notenschlüssel erzeugen
					for($i = 0; $i < sizeof($pupils); $i++) {
						$excelFile->setActiveSheetIndex(0)->setCellValue("A" . ($i+2),($this->getName($pupils[$i])));
					
						$sumCell = $this->colLetters[$aufgaben+1] . ($i+2);
						$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+1] . ($i+2),"=SUM(B" . ($i+2) . ":" . $this->colLetters[$aufgaben] . ($i+2) . ")");
						$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+2] . ($i+2),"=IF($sumCell>={$unten[1]},1,IF($sumCell>={$unten[2]},2,IF($sumCell>={$unten[3]},3,IF($sumCell>={$unten[4]},4,IF($sumCell>={$unten[5]},5,6)))))");
					}
					
					$excelFile->setActiveSheetIndex(0)->setCellValue("A" . (sizeof($pupils)+3),"Maximalpunkte");
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+1] . (sizeof($pupils)+3),"=SUM(B" . (sizeof($pupils)+3) . ":" . $this->colLetters[$aufgaben] . (sizeof($pupils)+3) . ")");
					
					$excelFile->getActiveSheet()->getStyle("A" . (sizeof($pupils)+3))->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle($this->colLetters[$aufgaben+1] . (sizeof($pupils)+3))->getFont()->setBold(true);
					
					$excelFile->getActiveSheet()->setTitle('Klasse ' . $grade);
					$excelFile->setActiveSheetIndex(0);
					
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+4] . "9","Schnitt");
					$excelFile->setActiveSheetIndex(0)->setCellValue($this->colLetters[$aufgaben+5] . "9","=AVERAGE(" . $this->colLetters[$aufgaben+2] . "2:" . $this->colLetters[$aufgaben+2] . (sizeof($pupils)+1) . ")");
					
					$excelFile->setActiveSheetIndex(0)->getStyle($this->colLetters[$aufgaben+5] . "9")->getNumberFormat()->setFormatCode('0.00');
					
					$excelFile->setActiveSheetIndex(0)->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
					$excelFile->setActiveSheetIndex(0)->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
					
					
					// Spaltenbreite automatisch setzen (Name bis Gesamtpunkte, Note)
					foreach(range('A',$this->colLetters[$aufgaben+7]) as $columnID) {
					    $excelFile->getActiveSheet()->getColumnDimension($columnID)
					        ->setAutoSize(true);
					}
					
					// Layout
					
					$BStyle = array(
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
							)
						)
					);
					
					$excelFile->getActiveSheet()->getStyle('A1:' . $this->colLetters[$aufgaben+2] . (sizeof($pupils)+1))->applyFromArray($BStyle);
					
					$excelFile->getActiveSheet()->getStyle("A" . (sizeof($pupils)+3) . ':' . $this->colLetters[$aufgaben+1] . (sizeof($pupils)+3))->applyFromArray($BStyle);
					
					$excelFile->getActiveSheet()->getStyle($this->colLetters[$aufgaben+4] . "1" . ':' . $this->colLetters[$aufgaben+8] . (7))->applyFromArray($BStyle);
					
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="Klassenliste_' . $grade . '.xlsx"');
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
					exit();
				}
				
				elseif(isset($_REQUEST['createXLSXdaten']) && $_REQUEST['createXLSXdaten'] != "") {
					error_reporting(0);
					// Excel
					$grade = $_REQUEST['grade'];
						
					$excelFile = new PHPExcel();
					// Set document properties
						
					$excelFile->getProperties()
					->setCreator(DB::getGlobalSettings()->siteNamePlain)
					->setTitle('Klassenliste ' . $grade)
					->setLastModifiedBy(DB::getGlobalSettings()->siteNamePlain)
					->setDescription('Klassenliste der Klasse ' . $grade);
						
					// Add some data
					$excelFile->setActiveSheetIndex(0)->setCellValue("A1","Nummer");
					$excelFile->setActiveSheetIndex(0)->setCellValue("B1","Name");
					$excelFile->setActiveSheetIndex(0)->setCellValue("C1","Vornamen");
					$excelFile->setActiveSheetIndex(0)->setCellValue("D1","Rufname");
					$excelFile->setActiveSheetIndex(0)->setCellValue("E1","Geburtsdatum");
					
					$excelFile->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle("B1")->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle("C1")->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle("D1")->getFont()->setBold(true);
					$excelFile->getActiveSheet()->getStyle("E1")->getFont()->setBold(true);
						

					for($i = 0; $i < sizeof($pupils); $i++) {
						$excelFile->setActiveSheetIndex(0)->setCellValue("A" . ($i+2),$i+1);
						$excelFile->setActiveSheetIndex(0)->setCellValue("B" . ($i+2),($pupils[$i]->getName()));
						$excelFile->setActiveSheetIndex(0)->setCellValue("C" . ($i+2),($pupils[$i]->getVornamen()));
						$excelFile->setActiveSheetIndex(0)->setCellValue("D" . ($i+2),($pupils[$i]->getRufname()));
						$excelFile->setActiveSheetIndex(0)->setCellValue("E" . ($i+2),($pupils[$i]->getGeburtstagAsNaturalDate()));
					}
						

					$excelFile->setActiveSheetIndex(0)->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
					$excelFile->setActiveSheetIndex(0)->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
						
						
					// Spaltenbreite automatisch setzen (Name bis Gesamtpunkte, Note)
					foreach(range('A',$this->colLetters[5]) as $columnID) {
						$excelFile->getActiveSheet()->getColumnDimension($columnID)
						->setAutoSize(true);
					}
						
					// Layout
						
					$BStyle = array(
							'borders' => array(
									'allborders' => array(
											'style' => PHPExcel_Style_Border::BORDER_THIN
									)
							)
					);
						
					$excelFile->getActiveSheet()->getStyle('A1:' . 'E' . (sizeof($pupils) + 1))->applyFromArray($BStyle);
						
												
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="Klassenliste_' . $grade . '.xlsx"');
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
					exit();
				}
			
		}
		
		// Klassenauswahl und Einstellungen anzeigen
		$gradeSelect = "";
		$grades = grade::getAllGrades();
		for($i = 0; $i < sizeof($grades); $i++) {
			$gradeSelect .= "<option value=\"" . $grades[$i] . "\"" . (($_GET['preSelectGrade'] != '' && $_GET['preSelectGrade'] == $grades[$i]) ? (" selected=\"selected\"") : ("")) . ">" . $grades[$i] . "</option>\n";
		}
		
		eval("echo(\"" . DB::getTPL()->get("klassenlisten/index"). "\");");
		
	}
	
	private function getName(schueler $row) {
		if(isset($_REQUEST['nameformat'])) {
			switch($_REQUEST['nameformat']) {
				case "nv":
				default:
					return $row->getName() . " " . $row->getRufname();
					
				case "nkv":
					return $row->getName() . ", " .  $row->getRufname();
				
				case "vn":
					return  $row->getRufname() . " " . $row->getName();
							
				case "vkn":
					return  $row->getRufname() . ", " . $row->getName();
					
				
			}
		}
		else {
			return $row->getName() . " " .  $row->getRufname();
		}
	}
	
	private function getTableTHForName() {
		if(isset($_REQUEST['nameformat'])) {
			switch($_REQUEST['nameformat']) {
				case "nv":
				default:
					return "Nachname Vorname";
						
				case "nkv":
					return "Nachname, Vorname";
		
				case "vn":
					return "Vorname Nachname";
						
				case "vkn":
					return "Vorname, Nachname";
						
		
			}
		}
		else {
			return "Nachname Vorname";
		}
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
		return 'Klassenlisten';
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
		return 'Webportal_Klassenlisten_Admin';
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