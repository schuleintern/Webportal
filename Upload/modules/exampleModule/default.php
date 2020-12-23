<?php

class exampleModuleDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Module';
	}


	public function __construct($request = []) {
		
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, 'module', $request);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();
		

		$this->render([
			"tmpl" => "default",
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
			"dropdown" => [
				[
					"url" => "index.php?page=exampleModule&task=print",
					"title" => "Drucken",
					"icon" => "fas fa-print"
				]
			]
		]);

	}

	/**
	 * Example Task Function
	 */
	public function taskPrint() {

		// Mach hier etwas cooles!!!

		$this->redirectWithoutParam('task');
	}

}
