<?php



class extUsersAdminUsers extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-user-shield"></i> Benutzer - System';
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


        $this->render([
            "tmpl" => "default",
            "dropdown" => [
                [
                    "url" => "index.php?page=ext_users&view=users&admin=true&task=exportAll",
                    "title" => "Export",
                    "icon" => "fa fa-download"
                ]
            ],
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/users/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/users/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/users",
                "acl" => $acl['rights']
            ]
        ]);

	}

    public function taskExportAll() {


        if ( !$this->canAdmin() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        //include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';
        $tmp_data = user::getAll();
        $name = tempnam('/tmp', 'csv');
        $handle = fopen($name, 'w');
        //$handle = fopen('php://temp', 'r+');
        //$ret = [];
        foreach ($tmp_data as $item) {
            $collection = $item->getCollection(true, true, true);
            //$ret[] = $collection;
            fputcsv($handle, $collection, ',', '"');
        }

        fclose($handle);

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=userlist.csv');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        ob_clean();
        flush();
        readfile($name);
        unlink($name);

    }

}
