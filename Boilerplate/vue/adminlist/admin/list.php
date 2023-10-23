<?php

class extPodcastAdminList extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-microphone-alt"></i> Podcast - Bearbeiten';
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
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "apiURL" => "rest.php/podcast",
                "acl" => $acl['rights']
            ]

        ]);

    }


}
