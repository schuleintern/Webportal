<?php



class extKrankmeldungAdminCalendar extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-sun"></i> Krankmeldung - Kalender';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
        $acl = $this->getAcl();

        if ( !$this->canAdmin() ) {
            new errorPage('Kein Zugriff');
        }

		
		$this->render([
            "tmpl" => "list",
			"scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/calendar/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/calendar/dist/js/app.js'
			],
			"data" => [
                "acl" => $acl['rights'],
				"selfURL" => URL_SELF,
                "apiURL" => "rest.php/krankmeldung"
			]

		]);

	}


}