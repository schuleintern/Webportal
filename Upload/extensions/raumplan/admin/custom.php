<?php

 

class extRaumplanAdminCustom extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-door-open"></i> Raumplan - Admin Raumauswahl';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

		//$this->getRequest();
		//$this->getAcl();


        $this->render([
            "tmpl" => "custom",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/default/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/raumplan"
            ]
        ]);

	}


}
