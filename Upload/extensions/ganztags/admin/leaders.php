<?php

 

class extGanztagsAdminLeaders extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa fa-users"></i> Ganztags - Team';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

        //$_request = $this->getRequest();
        $acl = $this->getAcl();

        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }

        //$user = DB::getSession()->getUser();

		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/leaders/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/leaders/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/ganztags",
                "acl" => $acl['rights']
		    ]
        ]);


	}


}
