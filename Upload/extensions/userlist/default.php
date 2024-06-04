<?php



class extUserlistDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa fa-user-check"></i> Benutzerlisten';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

        //$_request = $this->getRequest();
        //print_r($_request);

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            new errorPage('Kein Zugriff');
        }
        //print_r( $acl );

        $user = DB::getSession()->getUser();

		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/default/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/userlist",
                "acl" => $acl['rights']
		    ]
        ]);

	}

    function taskExport() {

        $tab_id = (int)$_GET['id'];
        if (!$tab_id) {
            new errorPage('Missing Tab ID');
        }

        $list_id = (int)$_GET['list_id'];
        if (!$list_id) {
            new errorPage('Missing List ID');
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            new errorPage('Kein Zugriff');
        }

        include_once PATH_EXTENSION . 'models' . DS . 'List.class.php';
        $data_list = extUserlistModelList::getByID($list_id, DB::getSession()->getUserID());

        if (!$data_list) {
            new errorPage('Missing List');
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Tab.class.php';
        $data_tab = extUserlistModelTab::getByID($tab_id, $list_id);

        if (!$data_tab) {
            new errorPage('Missing List');
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Content.class.php';
        $data_content = extUserlistModelContent::getMembersWithContentByTab($tab_id, $list_id);

        $today = date('d_m_Y', time() );
        $exportClass = new exportXls();
        $exportClass->setOptions([
            'title' => 'Benutzerliste ' . $today,
            'desc' => 'Benutzerliste '.$today,
            'creator' => DB::getGlobalSettings()->siteNamePlain,
            'modifiedBy' => DB::getGlobalSettings()->siteNamePlain
        ]);
        $excelFile = $exportClass->getSheet();
        /*
        include_once('../framework/lib/phpexcel/PHPExcel.php');
        
        $excelFile = new PHPExcel();
        $today = date('d_m_Y', time() );

        $excelFile->getProperties()
            ->setCreator(DB::getGlobalSettings()->siteNamePlain)
            ->setTitle('Benutzerliste '.$today )
            ->setLastModifiedBy(DB::getGlobalSettings()->siteNamePlain)
            ->setDescription('Export der Benutzerliste vom '.$today);
        */
        $excelFile->setActiveSheetIndex(0)->setCellValue("A1", 'Vorname');
        $excelFile->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("B1", 'Nachname');
        $excelFile->getActiveSheet()->getStyle("B1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("C1", 'Typ');
        $excelFile->getActiveSheet()->getStyle("C1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("D1", 'An/Aus');
        $excelFile->getActiveSheet()->getStyle("D1")->getFont()->setBold(true);
        $excelFile->setActiveSheetIndex(0)->setCellValue("E1", 'Info');
        $excelFile->getActiveSheet()->getStyle("E1")->getFont()->setBold(true);

        $type = [
            'isPupil' => 'Schüler',
            'isEltern' => 'Eltern',
            'isTeacher' => 'Lehrer',
            'isNone' => 'Sonstige'
        ];
        $i = 3;
        if (count($data_content) > 0) {
            foreach ($data_content as $item) {

                $collection = $item->getCollection(true);

                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$i, $collection['vorname'] );
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$i, $collection['nachname']);
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$i, $type[$collection['type']] );
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$i, $collection['toggle']);
                $excelFile->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$i, $collection['info']);

                $i++;
            }
        }

        $exportClass->output("Benutzerliste-".$data_list->getTitle()."-".$data_tab->getTitle().".xlsx");
        /*
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Benutzerliste-'.$data_list->getTitle().'-'.$data_tab->getTitle().'.xlsx"');
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
        */
        exit();


    }

    function taskPrint() {


        $tab_id = (int)$_GET['id'];
        if (!$tab_id) {
            new errorPage('Missing Tab ID');
        }

        $list_id = (int)$_GET['list_id'];
        if (!$list_id) {
            new errorPage('Missing List ID');
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            new errorPage('Kein Zugriff');
        }

        include_once PATH_EXTENSION . 'models' . DS . 'List.class.php';
        $data_list = extUserlistModelList::getByID($list_id, DB::getSession()->getUserID());

        if (!$data_list) {
            new errorPage('Missing List');
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Tab.class.php';
        $data_tab = extUserlistModelTab::getByID($tab_id, $list_id);

        if (!$data_tab) {
            new errorPage('Missing List');
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Content.class.php';
        $data_content = extUserlistModelContent::getMembersWithContentByTab($tab_id, $list_id);


        $html = '';
        $html .= '<style>
					table {
						width: 100%;
					}
					td { padding: 0.3rem; }
					</style>';
        //$html .= '<h3 style="text-align: right">'.$data_list->getTitle().'</h3>';
        $html .= '<h1>'.$data_tab->getTitle().'</h1>';
        $html .= '<h3 style="color:#ccc">'.$data_list->getTitle().'</h3>';
        $html .= '<br>';

        $html .= '<table cellspacing="0" cellpadding="5" border="0" style="border-color:white; border-collapse: collapse;" >
			<thead >
				<tr>
				    <th width="5%"></th>
					<th width="20%" style="font-weight: bold;">Vorname</th>
					<th width="20%" style="font-weight: bold;">Name</th>
					<th width="10%" style="font-weight: bold;"></th>
					<th width="" style="font-weight: bold;">Info</th>
				</tr>
			</thead>
			<tbody>';


        $num = 1;
        if (count($data_content) > 0) {
            foreach ($data_content as $item) {

                $collection = $item->getCollection(true);

                $style = '';
                $boder = 'border-right: 0.01px solid #ccc;';
                if ($num%2) {
                    $style = 'background-color: rgb(236, 240, 245); margin: 30px;';
                    $boder = 'border-right: 0.01px solid white';
                }


                $toggle = '';
                if ($collection['toggle']) {
                    $toggle = '<img src="./images/check-circle.svg" height="12px" width="12px"/>';
                }

                $html .= '<tr style="'.$style.'">';
                $html .= '<td width="5%" style="color:#ccc">'.$num.'</td>
                        <td width="20%" >'.$collection['vorname'].'</td>
                        <td width="20%" style="'.$boder.'">'.$collection['nachname'].'</td>
                        <td width="10%" style="text-align: center; '.$boder.'">'.$toggle.'</td>
                        <td width="">'.$collection['info'].'</td>
                      </tr>';

                $num++;
            }
        }
        $html .= '</tbody></table>';

        $pdf = new PrintNormalPageA4WithHeader('Benutzerliste-'.$data_list->getTitle().'-'.$data_tab->getTitle());
        $pdf->setPrintedDateInFooter();
        $pdf->setHTMLContent($html);
        $pdf->send();

        exit;


    }


}
