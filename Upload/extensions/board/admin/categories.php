<?php



class extBoardAdminCategories extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-chalkboard"></i> Boards - Liste';
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


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/categories/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/categories/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/board",
                "acl" => $acl['rights']
            ]
        ]);

	}

}