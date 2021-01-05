<?php

class exampleOverright extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Module - Overright';
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
			"tmpl" => "overright"
		]);

	}

}
