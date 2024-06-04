<?php

 

class extAusweisAdminAntrag extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fa-address-card"></i> Antrag - Liste';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		$acl = $this->getAcl();

        $user = DB::getSession()->getUser();

        if ( !$this->canWrite() ) {
            new errorPage('Kein Zugriff');
        }

		$this->render([
            "tmpl" => "default",
			"scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/antrag/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/antrag/dist/js/app.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
                "apiURL" => "rest.php/ausweis",
				"acl" => $acl['rights']
			]
		]);

	}

}
