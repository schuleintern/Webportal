<?php



class extFileCollectList extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-folder"></i> FileCollect - Gruppen';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }
    
    public function execute()
    {

        //$_request = $this->getRequest();
        //print_r($_request);
        //$user = DB::getSession()->getUser();

        $acl = $this->getAcl();
        //print_r( $acl );
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->isMember($this->getAdminGroup()) !== 1) {
            new errorPage('Kein Zugriff');
        }

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/app.js'
            ],
            "style" => [
                PATH_EXTENSION . 'tmpl/scripts/list/dist/css/app.css'
            ],
            "data" => [
                "apiURL" => "rest.php/filecollect",
                "acl" => $acl['rights']
            ]
        ]);
    }

    public function taskOpen () {

        $_request = $this->getRequest();
        if (!$_request['fiid']) { // file ID
            exit;
        }
        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->isMember($this->getAdminGroup()) !== 1) {
            new errorPage('Kein Zugriff');
        }
        include_once PATH_EXTENSION . 'models' . DS . 'File.class.php';
        extFilecollectModelFile::open($_request['fiid']);
        exit;
    }

    public function taskDownload () {

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->isMember($this->getAdminGroup()) !== 1) {
            new errorPage('Kein Zugriff');
        }
        $user = DB::getSession()->getUser();
        if (!$user->getUserID()) {
            new errorPage('Kein Zugriff');
        }

        $_request = $this->getRequest();
        if ($_request['foid']) { // folder ID
            include_once PATH_EXTENSION . 'models' . DS . 'Folder.class.php';
            extFilecollectModelFolder::downloadFilesAsZip($_request['foid'], $user->getUserID() );
            exit;
        }

        if ($_request['coid']) { // collection ID
            include_once PATH_EXTENSION . 'models' . DS . 'Collection.class.php';
            extFilecollectModelCollection::downloadFilesAsZip($_request['coid'], $user->getUserID() );
            exit;
        }


    }
}
