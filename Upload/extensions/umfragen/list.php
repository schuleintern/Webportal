<?php



class extUmfragenList extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-list"></i> Umfragen - Liste';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        $request = $this->getRequest();
        $acl = $this->getAcl();
        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/umfragen",
                "acl" => $acl['rights'],
                "lid" => $request['lid']
            ]
        ]);
    }

    private function getExportData($lid = false)
    {
        if (!$lid) {
            exit;
        }

        include_once PATH_EXTENSIONS . 'umfragen' . DS . 'models' . DS . 'List.class.php';
        $List = new extUmfragenModelList();

        include_once PATH_EXTENSIONS . 'umfragen' . DS . 'models' . DS . 'Answer.class.php';
        $Answer = new extUmfragenModelAnswer();

        $data = $List->getByID($lid);
        $item = $data->getCollection(true, true, true);

        if ($item && $item['userlist']) {
            $item['answers'] = [];

            foreach ($item['userlist'] as $key => $foo) {


                if ($foo['type'] == 'user') {

                    $tmp_answers = $Answer->getByParentAndUserID($lid, $foo['mid']);
                    if ($tmp_answers) {
                        $answers = [];
                        foreach ($tmp_answers as $answer) {
                            $answers[] = $answer->getCollection();
                        }
                        $item['answers'][$foo['mid']] = $answers;
                    }
                    //$foo['user'] = user::getCollectionByID($foo['mid'], true);
                } else if ($foo['type'] == 'inbox') {

                    $tmp_answers = $Answer->getByIdAndParent($lid, $foo['mid']);
                    if ($tmp_answers) {
                        $answers = [];
                        foreach ($tmp_answers as $answer) {
                            $answers[] = $answer->getCollection();
                        }
                        $item['answers'][$foo['mid']] = $answers;
                    }


                    $item['userlist'][$key]['user']['name'] = $foo['title'];

                }
            }
        }

        return $item;

    }

    public function taskPdf()
    {
        $request = $this->getRequest();
        $lid = (int)$request['lid'];
        if (!$lid) {
            exit;
        }

        $item = $this->getExportData($lid);

        $letters = array();
        $letter = 'A';
        while ($letter !== 'AAA') {
            $letters[] = $letter++;
        }

        $filename = 'Schule-Intern-Umfrage ' . $item['title'] . ' ' . date('Y-m-d_H-i', time());
        $exportClass = new exportPdf();
        $exportClass->setOptions([
            'title' => $filename,
            'desc' => 'Export der Umfrage ',
            'creator' => DB::getGlobalSettings()->siteNamePlain,
            'modifiedBy' => DB::getGlobalSettings()->siteNamePlain
        ]);
        $sheet = $exportClass->getSheet();


        $i = 3;
        $child_typs = [];
        if ($item['childs']) {
            foreach ($item['childs'] as $child) {
                $sheet->setActiveSheetIndex(0)->setCellValue($letters[$i] . '1', $child['sort'] . '.' . $child['title']);
                $sheet->setActiveSheetIndex(0)->getStyle($letters[$i] . '1')->getFont()->setBold(true);
                $i++;
                $child_typs[$child['id']] = $child['typ'];
            }
        }


        $i = 2;
        if ($item['userlist']) {
            foreach ($item['userlist'] as $user) {
                $sheet->setActiveSheetIndex(0)->setCellValue('A' . $i, $user['user']['name'].' '.$user['user']['klasse']);
                if ($user['user']['type'] == 'isEltern') {
                    $parentUser = user::getCollectionByID($user['user']['id'], true);
                    if ($parentUser['childs']) {
                        $childs = [];
                        foreach ($parentUser['childs'] as $child) {
                            $childs[] = $child['name'].' - '.$child['klasse'];
                        }
                        $sheet->setActiveSheetIndex(0)->setCellValue('B' . $i, 'Eltern von: '.join(', ',$childs));
                    }
                }
                $sheet->setActiveSheetIndex(0)->setCellValue('C' . $i, $user['klasse']);

                if ($item['answers'] && $item['answers'][$user['mid']]) {
                    $a = 3;
                    foreach ($item['answers'][$user['mid']] as $answer) {
                        if ( $child_typs[$answer['item_id']] == 'boolean' ) {
                            if ($answer['content'] == 2) {
                                $answer['content'] = 'nein';
                            } else if ($answer['content'] == 1) {
                                $answer['content'] = 'ja';
                            }
                        }
                        $sheet->setActiveSheetIndex(0)->setCellValue($letters[$a] . $i, $answer['content']);
                        $a++;
                    }
                }

                $i++;
            }
        }
        $exportClass->output($filename . ".pdf");
        ob_get_clean();
    }

    public function taskXlsx()
    {
        $request = $this->getRequest();
        $lid = (int)$request['lid'];
        if (!$lid) {
            exit;
        }

        $item = $this->getExportData($lid);

        $letters = array();
        $letter = 'A';
        while ($letter !== 'AAA') {
            $letters[] = $letter++;
        }

        $filename = 'Schule-Intern-Umfrage ' . $item['title'] . ' ' . date('Y-m-d_H-i', time());
        $exportClass = new exportXlsx();
        $exportClass->setOptions([
            'title' => $filename,
            'desc' => 'Export der Umfrage ',
            'creator' => DB::getGlobalSettings()->siteNamePlain,
            'modifiedBy' => DB::getGlobalSettings()->siteNamePlain
        ]);
        $sheet = $exportClass->getSheet();


        $i = 3;
        $child_typs = [];
        if ($item['childs']) {
            foreach ($item['childs'] as $child) {
                $sheet->setActiveSheetIndex(0)->setCellValue($letters[$i] . '1', $child['sort'] . '.' . $child['title']);
                $sheet->setActiveSheetIndex(0)->getStyle($letters[$i] . '1')->getFont()->setBold(true);
                $i++;
                $child_typs[$child['id']] = $child['typ'];
            }
        }


        $i = 2;
        if ($item['userlist']) {
            foreach ($item['userlist'] as $user) {
                $sheet->setActiveSheetIndex(0)->setCellValue('A' . $i, $user['user']['name'].' '.$user['user']['klasse']);
                if ($user['user']['type'] == 'isEltern') {
                    $parentUser = user::getCollectionByID($user['user']['id'], true);
                    if ($parentUser['childs']) {
                        $childs = [];
                        foreach ($parentUser['childs'] as $child) {
                            $childs[] = $child['name'].' - '.$child['klasse'];
                        }
                        $sheet->setActiveSheetIndex(0)->setCellValue('B' . $i, 'Eltern von: '.join(', ',$childs));
                    }
                }
                $sheet->setActiveSheetIndex(0)->setCellValue('C' . $i, $user['klasse']);

                if ($item['answers'] && $item['answers'][$user['mid']]) {
                    $a = 3;
                    foreach ($item['answers'][$user['mid']] as $answer) {
                        if ( $child_typs[$answer['item_id']] == 'boolean' ) {
                            if ($answer['content'] == 2) {
                                $answer['content'] = 'nein';
                            } else if ($answer['content'] == 1) {
                                $answer['content'] = 'ja';
                            }
                        }
                        $sheet->setActiveSheetIndex(0)->setCellValue($letters[$a] . $i, $answer['content']);
                        $a++;
                    }
                }

                $i++;
            }
        }
        $exportClass->output($filename . ".xlsx");
        ob_get_clean();
    }

    public function taskXls()
    {
        $request = $this->getRequest();
        $lid = (int)$request['lid'];
        if (!$lid) {
            exit;
        }

        $item = $this->getExportData($lid);

        $letters = array();
        $letter = 'A';
        while ($letter !== 'AAA') {
            $letters[] = $letter++;
        }

        $filename = 'Schule-Intern-Umfrage ' . $item['title'] . ' ' . date('Y-m-d_H-i', time());
        $exportClass = new exportXls();
        $exportClass->setOptions([
            'title' => $filename,
            'desc' => 'Export der Umfrage ',
            'creator' => DB::getGlobalSettings()->siteNamePlain,
            'modifiedBy' => DB::getGlobalSettings()->siteNamePlain
        ]);
        $sheet = $exportClass->getSheet();


        $i = 3;
        $child_typs = [];
        if ($item['childs']) {
            foreach ($item['childs'] as $child) {
                $sheet->setActiveSheetIndex(0)->setCellValue($letters[$i] . '1', $child['sort'] . '.' . $child['title']);
                $sheet->setActiveSheetIndex(0)->getStyle($letters[$i] . '1')->getFont()->setBold(true);
                $i++;
                $child_typs[$child['id']] = $child['typ'];
            }
        }


        $i = 2;
        if ($item['userlist']) {
            foreach ($item['userlist'] as $user) {
                $sheet->setActiveSheetIndex(0)->setCellValue('A' . $i, $user['user']['name'].' '.$user['user']['klasse']);
                if ($user['user']['type'] == 'isEltern') {
                    $parentUser = user::getCollectionByID($user['user']['id'], true);
                    if ($parentUser['childs']) {
                        $childs = [];
                        foreach ($parentUser['childs'] as $child) {
                            $childs[] = $child['name'].' - '.$child['klasse'];
                        }
                        $sheet->setActiveSheetIndex(0)->setCellValue('B' . $i, 'Eltern von: '.join(', ',$childs));
                    }
                }
                $sheet->setActiveSheetIndex(0)->setCellValue('C' . $i, $user['klasse']);

                if ($item['answers'] && $item['answers'][$user['mid']]) {
                    $a = 3;
                    foreach ($item['answers'][$user['mid']] as $answer) {
                        if ( $child_typs[$answer['item_id']] == 'boolean' ) {
                            if ($answer['content'] == 2) {
                                $answer['content'] = 'nein';
                            } else if ($answer['content'] == 1) {
                                $answer['content'] = 'ja';
                            }
                        }
                        $sheet->setActiveSheetIndex(0)->setCellValue($letters[$a] . $i, $answer['content']);
                        $a++;
                    }
                }

                $i++;
            }
        }
        $exportClass->output($filename . ".xls");
        ob_get_clean();
    }

    public function taskCsv()
    {
        $request = $this->getRequest();
        $lid = (int)$request['lid'];
        if (!$lid) {
            exit;
        }

        $item = $this->getExportData($lid);

        $letters = array();
        $letter = 'A';
        while ($letter !== 'AAA') {
            $letters[] = $letter++;
        }

        $filename = 'Schule-Intern-Umfrage ' . $item['title'] . ' ' . date('Y-m-d_H-i', time());

        $exportClass = new exportCsv($filename);
        $sheet = $exportClass->getSheet();

        if ($sheet) {

            $i = 2;
            $a = 2;
            $child_typs = [];
            if ($item['childs']) {
                $insertChild = [false,false,false,false];
                foreach ($item['childs'] as $key => $child) {
                    $child_typs[$child['id']] = $child['typ'];
                    $insertChild[$key + $a] = $child['sort'] . '.' . $child['title'];
                    $i++;
                }
                fputcsv($sheet, $insertChild, ';');
            }

            if ($item['userlist']) {

                foreach ($item['userlist'] as $user) {
                    $insertUser = [];

                    $insertUser[0] = $user['user']['name'];
                    $insertUser[1] = $user['user']['klasse'];

                    if ($user['user']['type'] == 'isEltern') {
                        $parentUser = user::getCollectionByID($user['user']['id'], true);
                        if ($parentUser['childs']) {
                            $childs = [];
                            foreach ($parentUser['childs'] as $child) {
                                $childs[] = $child['name'].' - '.$child['klasse'];
                            }
                            $insertUser[1] = 'Eltern von: '.join(', ',$childs);
                        }
                    }

                    if ($item['answers'] && $item['answers'][$user['mid']]) {
                        foreach ($item['answers'][$user['mid']] as $key => $answer) {
                            if ( $child_typs[$answer['item_id']] == 'boolean' ) {
                                if ($answer['content'] == 2) {
                                    $answer['content'] = 'nein';
                                } else if ($answer['content'] == 1) {
                                    $answer['content'] = 'ja';
                                }
                            }
                            $insertUser[$key +$a] = $answer['content'];
                            $a++;
                        }
                    }
                    fputcsv($sheet, $insertUser, ';');
                }
            }
        }
        $exportClass->output($filename . ".csv");
        exit;
    }

}
