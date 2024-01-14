<?php



class extCckForm extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-plug"></i> Content Creation Kit - Form';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


    public function execute() {

        $_request = $this->getRequest();
        //print_r($_request);

        if (!(int)$_request['id']) {
            new errorPage('Kein Zugriff (id)');
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            new errorPage('Kein Zugriff');
        }
        //print_r( $acl );

        //$user = DB::getSession()->getUser();


        include_once PATH_EXTENSION . 'models' . DS . 'Form.class.php';

        $form = extCckModelForm::getByID((int)$_request['id']);

        $formData = $form->getCollection(true);


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/form/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/cck",
                "acl" => $acl['rights'],
                "item" => $formData
            ]
        ]);

    }


}
