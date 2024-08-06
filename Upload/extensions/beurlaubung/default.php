<?php



class extBeurlaubungDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-sun"></i> Beurlaubung';
    }

    public function __construct($request = [], $extension = []) {
        parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
        $this->checkLogin();
    }


    public function execute()
    {

        //$_request = $this->getRequest();
        //print_r($_request);

        $acl = $this->getAcl();
        if ( !$this->canRead() ) {
            new errorPage('Kein Zugriff');
        }

        //print_r( $acl );

        //$user = DB::getSession()->getUser();


        $mySchueler = [];

        if (DB::getSession()->isPupil()) {

            $mySchueler_temp = DB::getSession()->getUser();
            $mySchueler[] = $mySchueler_temp->getCollection(true, false);

            $volljaehrige = DB::getSettings()->getBoolean("extBeurlaubung-volljaehrige-schueler");
            if ($volljaehrige == 1) {
                $acl['rights']['write'] = 0;
                $alter = (int)DB::getSession()->getPupilObject()->getAlter();
                if ($alter >= 18) {
                    $acl['rights']['write'] = 1;
                }
            }
        }


        if (DB::getSession()->isEltern()) {
            $mySchueler = [];
            $mySchueler_temp = DB::getSession()->getElternObject()->getMySchueler();
            foreach ($mySchueler_temp as $schueler) {
                $mySchueler[] = $schueler->getCollection(true, false);
            }
        }

        if (DB::getSession()->isTeacher()) {
            //$isSchulleitung = DB::getSession()->getTeacherObject()->isSchulleitung();
            $mySchueler = [];
            $mySchueler_temp = DB::getSession()->getUser();
            $mySchueler[] = $mySchueler_temp->getCollection(true, false);
        }

        if (DB::getSession()->isNone()) {
            $mySchueler = [];
            $mySchueler_temp = DB::getSession()->getUser();
            $mySchueler[] = $mySchueler_temp->getCollection(true, false);
        }



        $maxStunden = (int)DB::getSettings()->getValue("ext-stundenplan-anzahlstunden");
        if (!$maxStunden) {
            $maxStunden = (int)DB::getSettings()->getValue("stundenplan-anzahlstunden");
            if (!$maxStunden) {
                $maxStunden = 6;
            }
        }
        $stundenVormittag = 6;
        $stundenNachmittag = $maxStunden - $stundenVormittag;

        if ($stundenNachmittag < 0) {
            $stundenNachmittag = 0;
        }

        //$settings = $this->getSettings();
        //$user = DB::getSession()->getUser();

        $freigabeSL = DB::getSettings()->getBoolean("extBeurlaubung-schulleitung-freigabe");
        $freigabeKL = DB::getSettings()->getBoolean("extBeurlaubung-klassenleitung-freigabe");


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/beurlaubung",
                "acl" => $acl['rights'],
                "settings" => $this->getSettings(),
                "maxStunden" => (int)$maxStunden,
                "stundenVormittag" => (int)$stundenVormittag,
                "stundenNachmittag" => (int)$stundenNachmittag,
                "mySchueler" => $mySchueler,
                "freigabeKL" => (int)$freigabeKL,
                "freigabeSL" => (int)$freigabeSL,
                "hinweisAntragOpen" => nl2br(DB::getSettings()->getValue("extBeurlaubung-antrag-open")),
                "hinweisAntragOpenFinish" => nl2br(DB::getSettings()->getValue("extBeurlaubung-antrag-finish")),
                "formGanztags" => (int)DB::getSettings()->getValue("extBeurlaubung-form-ganztag"),
                "formGanztagsLabel" => (string)DB::getSettings()->getValue("extBeurlaubung-form-ganztag-label")

            ]
        ]);


    }


}
