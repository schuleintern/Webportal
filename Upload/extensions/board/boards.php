<?php



class extBoardBoards extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-chalkboard"></i> Boards - Übersicht';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


    public function submenu() {
        $ret = [];


        include_once PATH_EXTENSION . 'models' . DS . 'Category.class.php';
        $class = new extBoardModelCategory();
        $tmp_data = $class->getAllAllowed();
        foreach($tmp_data as $item) {
            //$data = $item->getCollection(true);

            $param = (object)[
                'view' => 'boards',
                'category' => $item->getID()
            ];
            $page = (object)[
                'page' => 'ext_board',
                'params' => $param
            ];
            $ret[] = (object)[
                'title' => $item->getData('title'),
                'icon' => 'fas fa-chalkboard-teacher',
                'url' => $page
            ];

        }
        return [
            "overright" => true,
            "data" => $ret
        ];
    }

	public function execute() {

		$request = $this->getRequest();
		//$this->getAcl();

        include_once PATH_EXTENSION . 'models' . DS . 'Category.class.php';
        $class = new extBoardModelCategory();
        $tmp_data = $class->getAllAllowed();
        $cats = [];
        foreach($tmp_data as $item) {
            $cats[] = $item->getCollection(true);
        }

        if ($request['category']) {
            $categoryTemp = $class->getByID($request['category']);
            if ($categoryTemp) {
                $category = $categoryTemp->getData('title');
            }
        }

        $acl = $this->getAcl();
        //$user = DB::getSession()->getUser();

        if ( !$this->canRead() ) {
            new errorPage('Kein Zugriff');
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
                "cats" => json_encode($cats),
                "activeCat" => $category
            ]

        ]);

	}

}