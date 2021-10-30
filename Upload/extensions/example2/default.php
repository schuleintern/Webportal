<?php

class example2Default extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example 2 Module - Default';
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

			"dropdown" => [
				[
					"url" => "index.php?page=example2&task=print",
					"title" => "Drucken",
					"icon" => "fas fa-print"
				]
			]
		]);

	}


}
