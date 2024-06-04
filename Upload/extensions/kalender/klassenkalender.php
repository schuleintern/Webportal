<?php

class extKalenderKlassenkalender extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


    public function execute() {

        // Leere Seite - dient als Platzhalter für den Menüpunkt

        $this->render([
            "tmplHTML" => ""
        ]);


    }


}
