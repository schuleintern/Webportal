<?php



class extGanztagsMy extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fas fa fa-users"></i> Ganztags - Meine Gruppe';
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


        $acl = $this->getAcl();
        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }
        //print_r( $acl );

        //$user = DB::getSession()->getUser();



        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/my/dist/js/app.js',
                PATH_EXTENSION . 'tmpl/scripts/my/dist/js/chunk-vendors.js'
            ],
            "data" => [
                "apiURL" => "rest.php/ganztags",
                "acl" => $acl['rights']
            ]
        ]);

    }


}
