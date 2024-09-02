<?php



class extGanztagsDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fas fa fa-users"></i> Ganztags - Schüler';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }


    public function execute()
    {

        //$_request = $this->getRequest();
        //print_r($_request);





        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->isMember($this->getAdminGroup()) !== 1) {
            new errorPage('Kein Zugriff');
        }
        //print_r( $acl );

        //$user = DB::getSession()->getUser();

        $showDays = array(
            'mo' => DB::getSettings()->getValue("ext_ganztags-day-mo"),
            'di' => DB::getSettings()->getValue("ext_ganztags-day-di"),
            'mi' => DB::getSettings()->getValue("ext_ganztags-day-mi"),
            'do' => DB::getSettings()->getValue("ext_ganztags-day-do"),
            'fr' => DB::getSettings()->getValue("ext_ganztags-day-fr"),
            'sa' => DB::getSettings()->getValue("ext_ganztags-day-sa"),
            'so' => DB::getSettings()->getValue("ext_ganztags-day-so")
        );

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/ganztags",
                "acl" => $acl['rights'],
                "showDays" => $showDays
            ]
        ]);

    }

    public function taskPrintList($postData)
    {

        $users_ids = explode(',', trim((string)$_POST['users']));

        if (!$users_ids || count($users_ids) < 1) {
            return false;
            exit;
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Activity2.class.php';
        $class = new extGanztagsModelActivity2();
        $groups = $class->getAll();

        $user_data = array();
        foreach ($users_ids as $user_id) {
            $user = user::getUserByID((int)$user_id);
            if ($user) {

                $data = $user->getCollection(true);

                //include_once 'Schueler2.class.php';

                $dataSQL = DB::getDB()->query_first("SELECT *  FROM ext_ganztags_schueler WHERE user_id =". (int)$user_id, true );

                if ($dataSQL['days']) {
                    $data['days'] = json_decode($dataSQL['days']);

                    foreach ($data['days'] as $key => $day) {
                        if ($day && $day->group) {
                            foreach ($groups as $group) {
                                if ($group->getID() == $day->group) {
                                    $day->group = $group->getData('title');
                                }
                            }
                        }
                    }
                }
                $user_data[] = $data;
            }
        }


        $showDays = array(
            'Mo' => DB::getSettings()->getValue("ext_ganztags-day-mo"),
            'Di' => DB::getSettings()->getValue("ext_ganztags-day-di"),
            'Mi' => DB::getSettings()->getValue("ext_ganztags-day-mi"),
            'Do' => DB::getSettings()->getValue("ext_ganztags-day-do"),
            'Fr' => DB::getSettings()->getValue("ext_ganztags-day-fr"),
            'Sa' => DB::getSettings()->getValue("ext_ganztags-day-sa"),
            'So' => DB::getSettings()->getValue("ext_ganztags-day-so")
        );

        $pdf = new PrintNormalPageA4WithoutHeader('Ganztags');
        $pdf->setPrintedDateInFooter();

        $html = '';
        $html .= '<style>
                            table {
                                width: 100%;
                            }
                            td { padding: 0.3rem; }
                            </style>';
        $html .= '<h1>Ganztags - Schüler</h1>';
        $html .= '<table cellspacing="0" cellpadding="5" border="0" style="border-color:white; border-collapse: collapse;" >
                                <thead >
                                    <tr>
                                        <th width="5%"></th>
                                        <th width="15%" style="font-weight: bold;">Vorname</th>
                                        <th width="15%" style="font-weight: bold;">Name</th>
                                        <th width="5%" style="font-weight: bold;"></th>
                                        <th width="8%" style="font-weight: bold;"></th>';

        foreach ($showDays as $day_title => $day_key) {
            if ($day_key == true) {
                $html .= '<th  style="font-weight: bold;">'.$day_title.'</th>';
            }
        }

        $html .= '</tr></thead><tbody>';

        if ($user_data) {
            $num = 1;
            foreach ($user_data as $schueler) {

                $style = '';
                $boder = 'border-right: 0.01px solid #ccc;';
                if ($num % 2) {
                    $style = 'background-color: rgb(236, 240, 245); margin: 30px;';
                    $boder = 'border-right: 0.01px solid white';
                }
                $html .= '<tr style="' . $style . '">';
                $html .= '<td width="5%" style="color:#ccc">' . $num . '</td>';

                $html .= '<td width="15%">' . $schueler['vorname'] . '</td>';
                $html .= '<td width="15%">' . $schueler['nachname'] . '</td>';

                $html .= '<td width="5%">';
                if ($schueler['gender'] == 'm') {
                    $html .= '<img src="./images/mars.svg" height="10px" width="10px"/>';
                } else if ($schueler['gender'] == 'w') {
                    $html .= '<img src="./images/venus.svg" height="10px" width="10px"/>';
                }
                $html .= '</td>';
                $html .= '<td width="8%" style="' . $boder . '">' . $schueler['klasse'] . '</td>';

                $row = '';
                foreach ($showDays as $day_title => $day_key) {
                    if ($day_key == true) {

                        if ($schueler['days']) {
                            foreach ($schueler['days'] as $key => $day) {
                                if ($key == strtolower($day_title) ) {
                                    $row = $day->group;
                                }
                            }

                        }
                        $html .= '<td  style="' . $boder . '; font-size:80%">'.$row.'</td>';
                    }

                }
                $html .= '</tr>';
                $num++;
            }
        }

        $html .= '</tbody></table>';
        $pdf->setHTMLContent($html);
        $pdf->send();
        exit;

    }


}
