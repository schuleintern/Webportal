<?php

 

class extSprechstundePlaner extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fa-calendar"></i> Sprechstunde - Planer';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

        //$_request = $this->getRequest();

        //print_r($_request);

        //print_r( $this->getAcl() );

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1) {
            new errorPage('Kein Zugriff');
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Helpers.class.php';

        $hourStart = DateTime::createFromFormat('H:i', extSprechstundeModelHelpers::getCalenderHourStart());
        $hourStart = (int)$hourStart->format('H');
        $hourCount = extSprechstundeModelHelpers::getCalenderHours();

        $showDays = array(
            'Mo' => DB::getSettings()->getValue('extSprechstunde-day-mo') || 0,
            'Di' => DB::getSettings()->getValue('extSprechstunde-day-di') || 0,
            'Mi' => DB::getSettings()->getValue('extSprechstunde-day-mi') || 0,
            'Do' => DB::getSettings()->getValue('extSprechstunde-day-do') || 0,
            'Fr' => DB::getSettings()->getValue('extSprechstunde-day-fr') || 0,
            'Sa' => DB::getSettings()->getValue('extSprechstunde-day-sa') || 0,
            'So' => DB::getSettings()->getValue('extSprechstunde-day-so') || 0,
        );


        $info = DB::getSettings()->getValue('extSprechstunde-planer-info-head');

		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/planer/dist/main.js'
            ],
            "vars" => [
                "info" => $info
            ],
            "data" => [
                "apiURL" => "rest.php/sprechstunde",
                "showDays" => $showDays,
                "acl" => $acl['rights'],
                "formData" => [
                    "hourStart" => $hourStart,
                    "hourCount" => $hourCount
                ]
		    ]
        ]);

	}


}
