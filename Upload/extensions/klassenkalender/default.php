<?php

class extKlassenkalenderDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa fa-calendar-alt"></i> Klassenkalender';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


    public function execute() {

        //$_request = $this->getRequest();
        //print_r($_request);

        $acl = $this->getAcl();



        if ( !$this->canRead() ) {
            new errorPage('Kein Zugriff');
        }

        //$ics = DB::getSettings()->getValue('extKalender-ics');

        $anzStunden = DB::getSettings()->getValue("ext-stundenplan-anzahlstunden");
        if (!$anzStunden) {
            $anzStunden = 6;
        }
        $stunden = [];
        for ($i = 1; $i <= $anzStunden; $i++) {
            $stunden[] = [
                'value' => $i,
                'title' => $i.'. Stunde'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Lnw.class.php';
        $LNWS = new extKlassenkalenderModelLnws();
        $lnw_options = [];
        $lnws = $LNWS->getAll();
        if ($lnws && count($lnws) > 0) {
            foreach ($lnws as $lnw) {
                $lnw_options[] = [
                    'value' => $lnw->getID(),
                    'title' => $lnw->getData('title')
                ];
            }
        }

        $retFach = [];
        $fachs = fach::getAllAktive();
        foreach ($fachs as $fach) {

            $retFach[] = [
                'title' => $fach->getKurzform(),
                'value' => $fach->getID()
            ];
        }

        $retTeacher = [];
        $teachers = lehrer::getAll();
        foreach ($teachers as $teacher) {
            $retTeacher[] = [
                'title' => $teacher->getKuerzel() . ' - ' . $teacher->getName(),
                'value' => $teacher->getID()
            ];
        }

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/kalender/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/kalender/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/klassenkalender",
                "acl" => $acl['rights'],
                "apiKey" => DB::getGlobalSettings()->apiKey,
                "isMobile" => $this->isMobile,
                "stunden" => $stunden,
                "lnw_options" => $lnw_options,
                "fach_options" => $retFach,
                "teachers_options" => $retTeacher
            ]
        ]);


    }




}
