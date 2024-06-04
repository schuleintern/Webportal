<?php



class extStundenplanAdminPlan extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-table"></i> Stundenplan - Plan';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();
        $acl = $this->getAcl();
        //$user = DB::getSession()->getUser();

        if ( !$this->canAdmin() ) {
            new errorPage('Kein Zugriff');
        }

        $retKlassen = [];
        $klassen = klasseDB::getAll();
        foreach ($klassen as $klasse) {
            $retKlassen[] = [
                'title' => $klasse->getKlassenname(),
                'value' => $klasse->getKlassenname()
            ];
        }

        $retFach = [];
        $fachs = fach::getAll();
        foreach ($fachs as $fach) {
            $retFach[] = [
                'title' => $fach->getKurzform(),
                'value' => $fach->getKurzform()
            ];
        }

        $retTeacher = [];
        $teachers = lehrer::getAll();
        foreach ($teachers as $teacher) {
            $retTeacher[] = [
                'title' => $teacher->getKuerzel().' - '.$teacher->getName(),
                'value' => $teacher->getKuerzel()
            ];
        }


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/plan/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/plan/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/stundenplan",
                "acl" => $acl['rights'],
                "id" => $this->getRequest()['id'],
                "anzStunden" => DB::getSettings()->getValue("ext-stundenplan-anzahlstunden"),
                "klassen" => json_encode($retKlassen),
                "fach" => json_encode($retFach),
                "teacher" => json_encode($retTeacher)
            ]
        ]);

	}

}
