<?php

class extExampleFrontend extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Module - Frontend';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

		
		$this->render([
			"tmpl" => "frontend",
			"scripts" => [
				PATH_EXTENSION.'tmpl/scripts/default/app.js'
			],
			"data" => [
				"testData" => "<b>Dieser Text wird mit Hilfe von JavaScript gesetzt</b>"
			]
		]);

	}

}
