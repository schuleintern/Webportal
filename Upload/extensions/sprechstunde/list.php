<?php



class extSprechstundeList extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fa-calendar"></i> Sprechstunde - Termine';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

        //$_request = $this->getRequest();

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1) {
            new errorPage('Kein Zugriff');
        }

        $user = DB::getSession()->getUser();

		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/list/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/sprechstunde",
                "acl" => $acl['rights'],
                "userSelf" => [
                    "typ" => $user->getUserTyp(true),
                    "id" => $user->getUserID()
                ]
		    ]
        ]);

	}


}
