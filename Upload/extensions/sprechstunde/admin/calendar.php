<?php



class extSprechstundeAdminCalendar extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-people-arrows"></i> Sprechstunde - Admin Kalender';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        $user = DB::getSession()->getUser();

        if ( !$user->isAnyAdmin() ) {
            new errorPage('Kein Zugriff');
        }

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

		$this->render([
			"tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
			"scripts" => [
                PATH_EXTENSION.'tmpl/scripts/calendar/dist/main.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"settings" => $this->getSettings()
			],
            "data" => [
                "apiURL" => "rest.php/sprechstunde",
                "showDays" => $showDays,
                "acl" => $acl['rights'],
                "userSelf" => [
                    "typ" => $user->getUserTyp(true),
                    "id" => $user->getUserID()
                ]
            ]

		]);

	}



}
