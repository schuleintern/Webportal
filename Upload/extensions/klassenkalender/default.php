<?php

 

class extKlassenKalenderDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa fa-calendar"></i> Klassen Kalender';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


    public function execute() {



        $this->render([
            "tmpl" => "default"
        ]);


    }

}
