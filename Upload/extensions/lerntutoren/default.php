<?php

 

class extLerntutorenDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fa-graduation-cap"></i> Verfügbare Lerntutoren';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        $showDays = array(
            'Mo' => DB::getSettings()->getValue("ganztags-day-mo"),
            'Di' => DB::getSettings()->getValue("ganztags-day-di"),
            'Mi' => DB::getSettings()->getValue("ganztags-day-mi"),
            'Do' => DB::getSettings()->getValue("ganztags-day-do"),
            'Fr' => DB::getSettings()->getValue("ganztags-day-fr"),
            'Sa' => DB::getSettings()->getValue("ganztags-day-sa"),
            'So' => DB::getSettings()->getValue("ganztags-day-so")
        );

		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/list/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/lerntutoren",
                "acl" => json_encode( $this->getAcl() ),
                "showDays"=> json_encode($showDays),
                "LANG_disclaimer" => nl2br(DB::getSettings()->getValue("ext-lerntutoren-disclaimer")),
                "LANG_show_text" => nl2br(DB::getSettings()->getValue("ext-lerntutoren-show-text"))
            ]
		]);

	}


}
