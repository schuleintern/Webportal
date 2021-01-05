<?php

class adminExampleCustom extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Admin - Settings';
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
			]
		]);

	}


}
