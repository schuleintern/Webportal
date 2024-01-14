<?php



class extLerntutorenMyslots extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fa-user"></i> Meine Lernangebote';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/myslots/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/lerntutoren",
                "LANG_myslots_hinweis" => nl2br(DB::getSettings()->getValue("ext-lerntutoren-myslots-hinweis"))
            ]
		]);

	}

	/**
	 * Example Task Function
	 */
	public function taskPrint() {

		// Mach hier etwas cooles!!!

		$this->reloadWithoutParam('task');
	}

}
