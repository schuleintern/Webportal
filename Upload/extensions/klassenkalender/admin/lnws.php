<?php

 

class extKlassenkalenderAdminLnws extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-graduation-cap"></i> Klassenkalender Leistungsnachweise - Bearbeiten';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        //$this->getRequest();
        $acl = $this->getAcl();



        if (!$this->canWrite()) {
            new errorPage('Kein Zugriff');
        }

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/lnws/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/lnws/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "apiURL" => "rest.php/klassenkalender",
                "acl" => $acl['rights'],
                "apiKey" => DB::getGlobalSettings()->apiKey,
                "isMobile" => $this->isMobile
            ]

        ]);

    }


}
