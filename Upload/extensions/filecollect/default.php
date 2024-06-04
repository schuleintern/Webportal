<?php



class extFileCollectDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-file-import"></i> FileCollect - Meine Uploads';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }
    
    public function execute()
    {


        //$d = DB::run( 'SELECT * FROM users WHERE userID = ?', [1608] )->fetchAll();

        //$d = DB::run( 'SELECT * FROM users WHERE userID = :id', ["id" => 1608] )->fetchAll();


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
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/filecollect",
                "acl" => $acl['rights']
            ]
        ]);
    }
}
