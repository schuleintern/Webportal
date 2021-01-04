<?php

class exampleList extends AbstractPage {
	
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
					"icon" => "fa fa-book"
				],
				[
					"admin" => true,
					"url" => "index.php?page=example&view=acl&admin=true",
					"title" => "Benutzerrechte",
					"icon" => "fa fa-book"
				],
				[
					"admin" => true,
					"url" => "index.php?page=example&view=custom&admin=true",
					"title" => "Admin Custom",
					"icon" => "fa fa-book"
				]
			],

			"dropdown" => []
		]);

	}

}
