<?php

 

class extUserSettings extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-user"></i> Profil - Einstellungen';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        $acl = $this->getAclAll();
        //$user = DB::getSession()->getUser();

        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "apiURL" => "rest.php/user",
                "acl" => $acl['rights']
            ]
        ]);
    }

}
