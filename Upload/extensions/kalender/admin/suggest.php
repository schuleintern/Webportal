<?php



class extKalenderAdminSuggest extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-plug"></i> Kalender - Vorschläge';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		$acl = $this->getAcl();

        //$user = DB::getSession()->getUser();

        if ( !$this->canAdmin() ) {
            new errorPage('Kein Zugriff');
        }

        $suggest = DB::getSettings()->getValue('extKalender-suggest');


        $this->render([
            "tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/suggest/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/suggest/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/kalender",
                "acl" => $acl['rights'],
                "suggest" => $suggest
            ]

        ]);

	}





}
