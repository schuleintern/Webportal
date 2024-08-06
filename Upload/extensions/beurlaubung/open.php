<?php

 

class extBeurlaubungOpen extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-sun"></i> Beurlaubung - Offene Anträge';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        $user = DB::getSession()->getUser();

        $freigabe = false;
        $freigabeSL = DB::getSettings()->getBoolean("extBeurlaubung-schulleitung-freigabe");
        if ($freigabeSL) {
            $schulleitung = schulinfo::getSchulleitungLehrerObjects();
            foreach ($schulleitung as $sl) {
                if ($sl->getUserID() == $user->getUserID()) {
                    $freigabe = true;
                }
            }
        }
        if ($freigabe == false) {
            $freigabeKL = DB::getSettings()->getBoolean("extBeurlaubung-klassenleitung-freigabe");
            if ($freigabeKL) {
                $freigabe = true;
            }
        }


        if ( DB::getSession()->isAdminOrGroupAdmin($this->extension['json']['adminGroupName']) === true ) {
            $freigabe = true;
        }

        $textVorlagen = DB::getSettings()->getValue("extBeurlaubung-done-vorlagen");
        $textVorlagen = explode(';', $textVorlagen);



        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/open/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/open/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "apiURL" => "rest.php/beurlaubung",
                "freigabe" => (int)$freigabe,
                "freigabeKL" => (int)$freigabeKL,
                "freigabeSL" => (int)$freigabeSL,
                "textVorlagen" => $textVorlagen
            ]

        ]);




	}


}
