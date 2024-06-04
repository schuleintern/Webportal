<?php



class extBoardAdminBoards extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-chalkboard"></i> Boards - Liste';
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

        if ( !$this->canAdmin() ) {
            new errorPage('Kein Zugriff');
        }


        include_once PATH_EXTENSIONS.'board'.DS . 'models' . DS . 'Category.class.php';
        $class = new extBoardModelCategory();
        $tmp_data = $class->getByState([1]);
        $categories = [];
        foreach($tmp_data as $item) {
            $categories[] = [
                "value" => $item->getID(),
                "title" => $item->getData('title')
            ];
        }



        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/boards/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/boards/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/board",
                "acl" => $acl['rights'],
                "categories" => $categories
            ]
        ]);

	}

    public function upload($data) {

        $id = (int)$data['id'];
        if (!$id) {
            return false;
        }
        $file = $_FILES['file'];
        if (!$file) {
            return [
                'error' => true,
                'msg' => 'Missing Files'
            ];
        }
        $target_Path = PATH_ROOT . 'data' . DS . 'ext_board' . DS;
        if (!file_exists($target_Path)) {
            mkdir($target_Path);
        }
        $target_Path = $target_Path . DS . $id . DS;
        if (!file_exists($target_Path)) {
            mkdir($target_Path);
        }
        $info = pathinfo($file['name']);
        $newname = 'podcast_' . time() . rand(100, 999) . '.' . $info['extension'];

        if (!move_uploaded_file($file['tmp_name'], $target_Path . $newname)) {
            echo 'Error'; exit;
        }
        echo $newname;
        exit;
    }

}