<?php



class extLerntutorenAdminCustom extends AbstractPage {

    public static function getSiteDisplayName() {
        return '<i class="fa fa-graduation-cap"></i> Verfügbare Lerntutoren - Admin';
    }

    public function __construct($request = [], $extension = []) {
        parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
        $this->checkLogin();
    }


    public function execute() {

        //$request = $this->getRequest();
        //$this->getAcl();


        $this->render([
            "tmpl" => "custom",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/list/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/lerntutoren"
            ],
            "dropdown" => [
                [
                    "url" => "index.php?page=ext_lerntutoren&view=custom&admin=true&task=exportExel",
                    "title" => "Export Exel",
                    "icon" => "fas fa-print"
                ]
            ]
        ]);

    }

    public function taskExportExel() {

        include_once PATH_EXTENSION . '../models' . DS . 'Slot.class.php';
        include_once PATH_EXTENSION . '../models' . DS . 'Tutoren.class.php';

        $items = extLerntutorenModelTutoren::getAllByStatus();

        /*
        include_once('../framework/lib/phpexcel/PHPExcel.php');

        $excelFile = new PHPExcel();
        $today = date('d_m_Y', time() );

        $excelFile->getProperties()
            ->setCreator(DB::getGlobalSettings()->siteNamePlain)
            ->setTitle('Lerntutoren '.$today )
            ->setLastModifiedBy(DB::getGlobalSettings()->siteNamePlain)
            ->setDescription('Export der Lerntutoren vom '.$today);
        */
        $today = date('d_m_Y', time() );
        $exportClass = new exportXls();
        $exportClass->setOptions([
            'title' => 'Lerntutoren_ ' . $today,
            'desc' => 'Lerntutoren_ '.$today,
            'creator' => DB::getGlobalSettings()->siteNamePlain,
            'modifiedBy' => DB::getGlobalSettings()->siteNamePlain
        ]);
        $excelFile = $exportClass->getSheet();

        $excelFile->setActiveSheetIndex(0)->setCellValue("A1", 'ID');
        $excelFile->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("B1", 'Status');
        $excelFile->getActiveSheet()->getStyle("B1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("C1", 'Name');
        $excelFile->getActiveSheet()->getStyle("C1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("D1", 'Fach');
        $excelFile->getActiveSheet()->getStyle("D1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("E1", 'Jahrgang');
        $excelFile->getActiveSheet()->getStyle("E1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("F1", 'Einheiten');
        $excelFile->getActiveSheet()->getStyle("F1")->getFont()->setBold(true);

        $excelFile->setActiveSheetIndex(0)->setCellValue("H1", 'Slot ID');
        $excelFile->getActiveSheet()->getStyle("H1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("I1", 'Status');
        $excelFile->getActiveSheet()->getStyle("I1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("J1", 'Name');
        $excelFile->getActiveSheet()->getStyle("J1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("K1", 'Einheiten');
        $excelFile->getActiveSheet()->getStyle("K1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("L1", 'Info');
        $excelFile->getActiveSheet()->getStyle("L1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("N1", 'Datum');
        $excelFile->getActiveSheet()->getStyle("N1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("O1", 'Dauer in min');
        $excelFile->getActiveSheet()->getStyle("O1")->getFont()->setBold(true);

        $i = 2;
        foreach ($items as $item) {

            $tutor = '';
            if ($item->getTutor()) {
                $tutor = $item->getTutor()->getCollection()['name'].' ('.$item->getTutor()->getCollection()['klasse'].')';
            }

            $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$i, $item->getID());
            $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$i, $item->getStatus());
            $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$i, $tutor);
            $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$i, $item->getFach());
            $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$i, $item->getJahrgang());
            $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$i, $item->getEinheiten());

            foreach ($item->getSlotsCollection() as $slot) {
                $i++;

                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$i, $slot['id']);
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$i, $slot['status']);
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$i, $slot['user']['name'].' ('.$slot['user']['klasse'].')');
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$i, $slot['einheiten']);
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$i, $slot['info']);
                //$excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$i, json_encode($slot['dates']));

                foreach ($slot['dates'] as $date) {
                    $i++;

                    $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$i, $date->date);
                    $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$i, $date->duration);
                }

            }
            $i++;
            $i++;
        }

        $exportClass->output("Lerntutoren_".$today.".xlsx");
        /*
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Lerntutoren_'.$today.'.xlsx"');
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

}
