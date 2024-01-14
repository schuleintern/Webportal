<?php

 

class extDependenciesDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Dependencies Extension';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {


        // dependencies models werden automatisch geladen

		$data = extExampleModelItem::getStaticData();

		$this->render([
			"tmpl" => "default",
            "vars" => [
              "data" => $data
            ]
		]);

	}


}
