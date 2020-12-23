<?php

class exampleModuleList extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Module - List View';
	}


	public function __construct($request = []) {
		
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, 'module', $request);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getAcl();
		
		$this->render([
			"tmpl" => "list",
			"script" => [
				'dist/js/app.js'
			],
			"data" => [
				"testData" => "Test Data"
			],
			"submenu" => [
				[
					"url" => "index.php?page=exampleModule",
					"title" => "Default",
					"icon" => "fa fa-cogs"
				],
				[
					"url" => "index.php?page=exampleModule&view=list",
					"title" => "List",
					"icon" => "fa fa-book"
				]
			],
			"dropdown" => []
		]);

	}

}
