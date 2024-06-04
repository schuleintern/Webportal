<?php

 

class extExampleAcl extends AbstractPage {

	public static function getSiteDisplayName() {
		return 'Example Extension - Acl';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

		//$this->getRequest();

		$this->render([
			"tmpl" => "acl",
            "vars" => [
                "acl" => $this->getAcl(),
                "aclAll" => $this->getAclAll()
            ]
		]);

	}



}
