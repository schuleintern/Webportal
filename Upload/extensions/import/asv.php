<?php

 

class extImportAsv extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '';
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

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }


        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $lastASV = DB::getSettings()->getValue("last-asv-import");

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/asv/dist/js/app.js',
                PATH_EXTENSION . 'tmpl/scripts/asv/dist/js/chunk-vendors.js'
            ],
            "data" => [
                "acl" => $acl['rights'],
                "apiURL" => "rest.php/import",
                "randFile" => substr(md5(rand()), 0, 16),
                "lastASV" => $lastASV
            ]
        ]);

    }


}
