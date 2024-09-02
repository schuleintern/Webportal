<?php

 

class extAkteAdminTags extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-tags"></i> Akte - Schlagworte';
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
                PATH_EXTENSION . 'tmpl/scripts/tags/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/tags/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "apiURL" => "rest.php/akte",
                "acl" => $acl['rights']
            ]

        ]);

    }


}
