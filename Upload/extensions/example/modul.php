<?php



class extExampleModul extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Extension - Modul-Klassen';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

        // Autoload Extension Modules

        $class = new extExampleModelItem();
        // print_r( $class->getData() );
        // print_r( extExampleModelItem::getStaticData() );

		$this->render([
			"tmpl" => "modul",
            "vars" => [
                "data_class" => $class->getData(),
                "data_static" => extExampleModelItem::getStaticData()
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
