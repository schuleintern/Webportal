<?php

 

class extMessageDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fa-envelope"></i> Nachrichten';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/list/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/message",
                "PATH_COMPONENTS" => './../../../../../www/components/'
            ]
		]);

	}


}
