<?php



class extSanctionsRules extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa fa-gavel"></i> Sanktionen - Regeln';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


    public function execute() {

        //$_request = $this->getRequest();
        //print_r($_request);

        $acl = $this->getAcl();


        $count = (int)DB::getSettings()->getValue('extSanctions-count-anzahl');
        if (!$count) {
            $count = 3;
        }

        $text = (string)DB::getSettings()->getValue('extSanctions-rules-text');

        //print_r( $acl );

        //$user = DB::getSession()->getUser();

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/rules/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/rules/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/sanctions",
                "acl" => $acl['rights'],
                "count" => $count,
                "text" => $text
            ]
        ]);


    }

}
