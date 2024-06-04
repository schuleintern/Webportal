<?php



class extPausenAufsicht extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-pause-circle"></i> Pausen - Aufsicht';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        $acl = $this->getAcl();
        //$user = DB::getSession()->getUser();

        if ( !$this->canRead() ) {
            new errorPage('Kein Zugriff');
        }

        include_once PATH_EXTENSIONS.'pausen'.DS . 'models' . DS . 'Pausen.class.php';
        $class = new extPausenModelPausen();
        $tmp_data = $class->getByState([1]);

        $options = [];
        foreach ($tmp_data as $item) {
            $options[] = [
                'title' => $item->getData('title'),
                'value' => $item->getID()
            ];
        }

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/aufsicht/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/aufsicht/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/pausen",
                "acl" => $acl['rights'],
                "optionsPausen" => json_encode($options)
            ]
        ]);

	}

}