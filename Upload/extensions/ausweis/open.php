<?php



class extAusweisOpen extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-address-card"></i> Offene Anträge für Klassenleiter*innen';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        $acl = $this->getAcl();
        $user = DB::getSession()->getUser();

        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }

        if ( !$user->isTeacher() && !$user->isAdmin() ) {
            new errorPage('Kein Zugriff');
        }

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/open/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/open/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "apiURL" => "rest.php/ausweis",
                "acl" => $acl['rights']
            ]
        ]);
    }


}
