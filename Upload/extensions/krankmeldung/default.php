<?php


class extKrankmeldungDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-bed"></i> Krankmeldung';
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
        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }
        //$freigabeKL = DB::getSettings()->getBoolean("extBeurlaubung-klassenleitung-freigabe");


        $listUsers = [];

        if (DB::getSession()->isPupil()) {

            $acl['rights']['write'] = 0;
            $volljaehrige = DB::getSettings()->getBoolean("extKrankmeldung-form-volljaehrige");
            if ($volljaehrige == 1) {
                $alter = (int)DB::getSession()->getPupilObject()->getAlter();
                if ($alter >= 18) {
                    $listUsers_temp = DB::getSession()->getUser();
                    $listUsers[] = $listUsers_temp->getCollection(true, false);
                    $acl['rights']['write'] = 1;
                }
            }


        }

        if (DB::getSession()->isEltern()) {
            $listUsers_temp = DB::getSession()->getElternObject()->getMySchueler();
            foreach ($listUsers_temp as $item) {
                $listUsers[] = $item->getCollection(true, false);
            }
        }

        if (DB::getSession()->isTeacher()) {
            $listUsers_temp = DB::getSession()->getUser();
            $listUsers[] = $listUsers_temp->getCollection(true, false);
        }

        if (DB::getSession()->isNone()) {
            $listUsers_temp = DB::getSession()->getUser();
            $listUsers[] = $listUsers_temp->getCollection(true, false);
        }

        $listDateStart = [];
        $today = DateFunctions::getTodayAsNaturalDate();
        while (DateFunctions::getWeekDayFromNaturalDate($today) == 0 || DateFunctions::getWeekDayFromNaturalDate($today) == 6 || ferien::isFerien(DateFunctions::getMySQLDateFromNaturalDate($today))) {
            $today = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addOneDayToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($today)));
        }
        $listDateStart[] = [
            "title" => functions::getDayName(DateFunctions::getWeekDayFromNaturalDate($today) - 1) . ", " . $today,
            "value" => DateFunctions::getMySQLDateFromNaturalDate($today)
        ];
        $nextDay = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addOneDayToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($today)));
        while (DateFunctions::getWeekDayFromNaturalDate($nextDay) == 0 || DateFunctions::getWeekDayFromNaturalDate($nextDay) == 6 || Ferien::isFerien(DateFunctions::getMySQLDateFromNaturalDate($nextDay)) != null) {
            $nextDay = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::addOneDayToMySqlDate(DateFunctions::getMySQLDateFromNaturalDate($nextDay)));
        }
        $listDateStart[] = [
            "title" => functions::getDayName(DateFunctions::getWeekDayFromNaturalDate($nextDay) - 1) . ", " . $nextDay,
            "value" => DateFunctions::getMySQLDateFromNaturalDate($nextDay)
        ];


        $listDateAdd = [];
        $tageMax = DB::getSettings()->getValue("extKrankmeldung-form-tage-max");
        if ($tageMax) {
            for($i = 1; $i <= $tageMax; $i++) {
                $arr = [
                    "title" => $i." Tag",
                    "value" => $i
                ];
                $i != 1 ? $arr['title'] .= 'e' : false;
                $listDateAdd[] = $arr;
            }
        }


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/krankmeldung",
                "acl" => $acl['rights'],
                "listUsers" => $listUsers,
                "listDateStart" => $listDateStart,
                "listDateAdd" => $listDateAdd,
                "hinweisForm" => nl2br(DB::getSettings()->getValue("extKrankmeldung-form-hinweis")),
                "hinweisFormBemerkung" => nl2br(DB::getSettings()->getValue("extKrankmeldung-form-info-hinweis")),
                "hinweisStatus" => DB::getSettings()->getBoolean("extKrankmeldung-form-info-status")
            ]
        ]);


    }


}
