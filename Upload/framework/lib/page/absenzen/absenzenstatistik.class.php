<?php


 

class absenzenstatistik extends AbstractPage {

	private $stundenplan = null;
	private $stundenplanActiveKlasse = null;
	
	private $isSekretariat = false;
	
	public function __construct() {
		
		$this->needLicense = false;
		
		parent::__construct(array("Absenzenverwaltung", "Statistik"));
		
		$this->checkLogin();
	
		$this->isSekretariat = DB::getSession()->isMember("Webportal_Absenzen_Sekretariat");
		
		if(DB::getSession()->isAdmin()) $this->isSekretariat = true;
		
		if(!$this->isSekretariat) die("No Access!");
		
	}

	public function execute() {
		include_once("../framework/lib/data/absenzen/Absenz.class.php");
		include_once("../framework/lib/data/absenzen/AbsenzBefreiung.class.php");
		include_once("../framework/lib/data/absenzen/AbsenzBeurlaubung.class.php");
		include_once("../framework/lib/data/absenzen/AbsenzSchuelerInfo.class.php");

        if ($_GET['task'] == 'pdf') {
            $this->exportIndex();
            exit;
        }
		$this->showIndex();
	}
		
	public function showIndex() {
		$schuelerMitAbsenzen = DB::getDB()->query("SELECT DISTINCT absenzSchuelerAsvID FROM absenzen_absenzen");		
		$asvIDs = array();
		
		while($s = DB::getDB()->fetch_array($schuelerMitAbsenzen)) $asvIDs[] = $s['absenzSchuelerAsvID'];
		
		$tableData = "";
		
		for($i = 0; $i < sizeof($asvIDs); $i++) {
			$schueler = schueler::getByAsvID($asvIDs[$i]);
			if($schueler != null) {
				$tableData .= "<tr>";
				$tableData .= "<td>" . $schueler->getCompleteSchuelerName() . " (<a href=\"index.php?page=absenzensekretariat&mode=editAbsenzen&schuelerAsvID=" . $schueler->getAsvID() . "\">Anzeigen</a>)</td>";
				$tableData .= "<td>" . $schueler->getKlasse() . "</td>";
				

				
				$gesamt = 0;
				$urlaub = 0;
				$fpA = 0;
				$absenzen = Absenz::getAbsenzenForSchueler($schueler);
				for($a = 0; $a < sizeof($absenzen); $a++) {
					$gesamt += $absenzen[$a]->getTotalDays();
					$urlaub += $absenzen[$a]->getBeurlaubungTage();
					$fpA += $absenzen[$a]->getTotalDaysNotAnwesend();
				}
				
				$tableData .= "<td>" . $gesamt . "</td>";
				
				if(DB::getSettings()->getBoolean('absenzen-has-fpa')) {
					$tableData .= "<td>" . $fpA . "</td>";
				}
				
				
				$tableData .= "<td>" . $urlaub . "</td>";
				$tableData .= "<td>" . ($gesamt-$urlaub) . "</td>";
				
				$sani = DB::getDB()->query_first("SELECT count(*) AS ANZAHL FROM absenzen_sanizimmer WHERE sanizimmerSchuelerAsvID='" . $schueler->getAsvID() . "'");
				$verspaetungen = DB::getDB()->query_first("SELECT count(*) AS ANZAHL FROM absenzen_verspaetungen WHERE verspaetungSchuelerAsvID='" . $schueler->getAsvID() . "'");
				
				$tableData .= "<td>" . $sani['ANZAHL'] . "</td>";
				$tableData .= "<td>" . $verspaetungen['ANZAHL'] . "</td></tr>";
			}
		}
		
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/statistik/index") . "\");");
	}

    public function exportIndex() {



        $schuelerMitAbsenzen = DB::getDB()->query("SELECT DISTINCT absenzSchuelerAsvID FROM absenzen_absenzen");
        $asvIDs = array();

        while($s = DB::getDB()->fetch_array($schuelerMitAbsenzen)) $asvIDs[] = $s['absenzSchuelerAsvID'];

/*
        include_once('../framework/lib/phpexcel/PHPExcel.php');

        $excelFile = new PHPExcel();
        $today = date('d_m_Y', time() );

        $excelFile->getProperties()
            ->setCreator(DB::getGlobalSettings()->siteNamePlain)
            ->setTitle('Absenzen Statistik '.$today )
            ->setLastModifiedBy(DB::getGlobalSettings()->siteNamePlain)
            ->setDescription('Absenzen Statistik vom '.$today);
*/
        $today = date('d_m_Y', time() );
        $exportClass = new exportXls();
        $exportClass->setOptions([
            'title' => 'Absenzen Statistik ' . $today,
            'desc' => 'Absenzen Statistik vom '.$today,
            'creator' => DB::getGlobalSettings()->siteNamePlain,
            'modifiedBy' => DB::getGlobalSettings()->siteNamePlain
        ]);
        $excelFile = $exportClass->getSheet();

        $excelFile->setActiveSheetIndex(0)->setCellValue("A1", 'Schüler');
        $excelFile->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("B1", 'Klasse');
        $excelFile->getActiveSheet()->getStyle("B1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("C1", 'Absenzentage Gesamt');
        $excelFile->getActiveSheet()->getStyle("C1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("D1", 'Absenzentage (beurlaubt)');
        $excelFile->getActiveSheet()->getStyle("D1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("E1", 'Absenzentage (sonstige)');
        $excelFile->getActiveSheet()->getStyle("E1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("F1", 'Aufenthalte Krankenzimmer');
        $excelFile->getActiveSheet()->getStyle("F1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("G1", 'Befreiungen');
        $excelFile->getActiveSheet()->getStyle("G1")->getFont()->setBold(true);

        $z = 2;


        for($i = 0; $i < sizeof($asvIDs); $i++) {
            $schueler = schueler::getByAsvID($asvIDs[$i]);
            if($schueler != null) {


                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$z, $schueler->getCompleteSchuelerName() );
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$z, $schueler->getKlasse() );

                $gesamt = 0;
                $urlaub = 0;
                $absenzen = Absenz::getAbsenzenForSchueler($schueler);
                for($a = 0; $a < sizeof($absenzen); $a++) {
                    $gesamt += $absenzen[$a]->getTotalDays();
                    $urlaub += $absenzen[$a]->getBeurlaubungTage();
                }

                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$z, $gesamt );
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$z, $urlaub );
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$z, ($gesamt-$urlaub) );


                $sani = DB::getDB()->query_first("SELECT count(*) AS ANZAHL FROM absenzen_sanizimmer WHERE sanizimmerSchuelerAsvID='" . $schueler->getAsvID() . "'");
                $verspaetungen = DB::getDB()->query_first("SELECT count(*) AS ANZAHL FROM absenzen_verspaetungen WHERE verspaetungSchuelerAsvID='" . $schueler->getAsvID() . "'");

                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$z, $sani['ANZAHL'] );
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$z, $verspaetungen['ANZAHL'] );


                $z++;

            }
        }



        $exportClass->output("Absenzen_Statistik_".$today.".xlsx");

    /*
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Absenzen_Statistik_'.$today.'.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objWriter = PHPExcel_IOFactory::createWriter($excelFile, 'Excel2007');
        $objWriter->save('php://output');
        */
        exit();



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
		return array(
				array(
					'name' => "absenzen-attestnachdreitagen",
					'typ' => BOOLEAN,
					'titel' => "Attest nach 3 Tagen fordern?",
					'text' => "Soll ein Attest nach drei Tagen Abwesenheit gefordert werden?"
				)
		);
	}
	
	
	public static function getSiteDisplayName() {
		return 'Absenzenmodul (Statistik)';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];
	}
	
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Absenzen_Admin';
	}
	
	public static function dependsPage() {
		return ['absenzenberichte','absenzensekretariat'];
	}
	
	
}


?>