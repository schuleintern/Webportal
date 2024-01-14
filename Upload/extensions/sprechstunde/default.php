<?php

 

class extSprechstundeDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-people-arrows"></i> Sprechstunde - Kalender';
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

        //print_r($user->getUserTyp(true));

        $showDays = array(
            'Mo' => DB::getSettings()->getValue('extSprechstunde-day-mo') || 0,
            'Di' => DB::getSettings()->getValue('extSprechstunde-day-di') || 0,
            'Mi' => DB::getSettings()->getValue('extSprechstunde-day-mi') || 0,
            'Do' => DB::getSettings()->getValue('extSprechstunde-day-do') || 0,
            'Fr' => DB::getSettings()->getValue('extSprechstunde-day-fr') || 0,
            'Sa' => DB::getSettings()->getValue('extSprechstunde-day-sa') || 0,
            'So' => DB::getSettings()->getValue('extSprechstunde-day-so') || 0,
        );

        $info = DB::getSettings()->getValue('extSprechstunde-calendar-info-head');

        $medium = [
            'phone' => DB::getSettings()->getValue('extSprechstunde-form-medium-phone') || 0,
            'viko' => DB::getSettings()->getValue('extSprechstunde-form-medium-viko') || 0
        ];


        

		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/default/dist/main.js'
            ],
            "vars" => [
                "info" => $info
            ],
            "data" => [
                "apiURL" => "rest.php/sprechstunde",
                "showDays" => $showDays,
                "acl" => $acl['rights'],
                "userSelf" => [
                    "typ" => $user->getUserTyp(true),
                    "id" => $user->getUserID()
                ],
                "medium" => $medium
		    ]
        ]);

	}


}
