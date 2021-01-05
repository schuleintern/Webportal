<?php

class exampleFrontend extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Module - Frontend';
	}

	public static function getAdminGroup() {
		return 'Admin_Extension_Example';
	}
	
	public function aclModuleName() {
		return 'extension_example';
	}
	
	public function __construct($request = []) {
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
