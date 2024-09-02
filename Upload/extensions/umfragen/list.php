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

    public function taskXls()
    {
        $request = $this->getRequest();
        $lid = (int)$request['lid'];
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

            foreach ($item['userlist'] as $user) {
                $tmp_answers = $Answer->getByParentAndUserID($item['id'], $user['id']);
                if ($tmp_answers) {
                    $answers = [];
                    foreach ($tmp_answers as $answer) {
                        $answers[] = $answer->getCollection();
                    }
                    $item['answers'][$user['id']] = $answers;
                }
            }
        }

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

        foreach ($item['childs'] as $child) {
            $sheet->setActiveSheetIndex(0)->setCellValue($letters[$i] . '1', $child['sort'] . '.' . $child['title']);
            $sheet->setActiveSheetIndex(0)->getStyle($letters[$i] . '1')->getFont()->setBold(true);
            $i++;
        }

        $i = 2;
        foreach ($item['userlist'] as $user) {
            $sheet->setActiveSheetIndex(0)->setCellValue('A' . $i, $user['name']);
            if ($user['type'] == 'isEltern') {
                if ($user['childs']) {
                    $childs = [];
                    foreach ($user['childs'] as $child) {
                        $childs[] = $child['name'];
                    }
                    $sheet->setActiveSheetIndex(0)->setCellValue('B' . $i, 'Eltern von: '.join(', ',$childs));
                }
            }
            $sheet->setActiveSheetIndex(0)->setCellValue('C' . $i, $user['klasse']);

            if ($item['answers'] && $item['answers'][$user['id']]) {
                $a = 3;
                foreach ($item['answers'][$user['id']] as $answer) {
                    $sheet->setActiveSheetIndex(0)->setCellValue($letters[$a] . $i, $answer['content']);
                    $a++;
                }
            }

            $i++;
        }

        $exportClass->output($filename . ".xlsx");

    }

}
