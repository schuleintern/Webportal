<?php

 

class extPodcastDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-microphone-alt"></i> Podcast';
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


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js'
            ],
            "data" => [
                "acl" => $acl['rights'],
                "apiURL" => "rest.php/podcast",
                "fileURL" => PATH_ROOT . 'data' . DS . 'ext_podcast' . DS
            ]
        ]);



    }



}
