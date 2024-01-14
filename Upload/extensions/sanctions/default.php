<?php



class extSanctionsDefault extends AbstractPage {
	
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

        $userID = DB::getSession()->getUser()->getUserID();

        $count = (int)DB::getSettings()->getValue('extSanctions-count-anzahl');
        if (!$count) {
            $count = 3;
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Users.class.php';
        $data = extSanctionsModelUser::getByUserID($userID);

        $ret = [];
        if ($data) {
            $ret =  $data->getCollection(true);
        }



        //print_r( $acl );

        //$user = DB::getSession()->getUser();

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/sanctions",
                "acl" => $acl['rights'],
                "data" => json_encode($ret),
                "count" => (int)$count
            ]
        ]);


    }

}
