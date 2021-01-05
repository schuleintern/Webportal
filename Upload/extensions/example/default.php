<?php

class exampleDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Module';
	}


	public function __construct($request = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

		//$this->getRequest();
		//$this->getAcl();
		

		$this->render([
			"tmpl" => "default",
			"scripts" => [
				'dist/js/app.js'
			],
			"data" => [
				"testData" => "Test Data"
			],
			"submenu" => [
				[
					"url" => "index.php?page=example",
					"title" => "Default",
					"icon" => "fa fa-cogs"
				],
				[
					"url" => "index.php?page=example&view=list",
					"title" => "List",
					"icon" => "fa fa-book"
				],

				[
					"admin" => true,
					"url" => "index.php?page=example&view=default&admin=true",
					"title" => "Einstellungen",
					"icon" => "fa fa-sliders-h"
				],
				[
					"admin" => true,
					"url" => "index.php?page=example&view=acl&admin=true",
					"title" => "Benutzerrechte",
					"icon" => "fa fa-user-shield"
				],
				[
					"admin" => true,
					"url" => "index.php?page=example&view=custom&admin=true",
					"title" => "Admin Custom",
					"icon" => "fa fa-cog"
				]
			],

			"dropdown" => [
				[
					"url" => "index.php?page=example&task=print",
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

		//echo 'HIER!'; exit;

		$this->redirectWithoutParam('task');
	}

}
