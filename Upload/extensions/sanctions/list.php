<?php



class extSanctionsList extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa fa-gavel"></i> Sanktionen';
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

        $count = (int)DB::getSettings()->getValue('extSanctions-count-anzahl');
        if (!$count) {
            $count = 3;
        }

        //print_r( $acl );

        //$user = DB::getSession()->getUser();

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/sanctions",
                "acl" => $acl['rights'],
                "count" => $count
            ]
        ]);


    }

}
