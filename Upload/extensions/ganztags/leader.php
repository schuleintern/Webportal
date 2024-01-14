<?php

 

class extGanztagsLeader extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa fa-users"></i> Ganztags - Gruppenleiterin';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

        //$_request = $this->getRequest();
        //print_r($_request);


        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->isMember($this->getAdminGroup()) !== 1 ) {
            new errorPage('Kein Zugriff');
        }
        //print_r( $acl );

        $user = DB::getSession()->getUser();

		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/leader/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/ganztags",
                "acl" => $acl['rights']
		    ]
        ]);

	}


}
