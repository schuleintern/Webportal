<?php

class extExampleOverwrite extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Module - overwrite';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

		
		$this->render([
			"tmpl" => "overwrite"
		]);

	}

}
