<?php

class extKalenderAppGetDefault extends AbstractApp
{	

    public function execute() {

        return [
            "scripts" => [
                PATH_EXTENSIONS . '/kalender/tmpl/scripts/kalender/dist/js/chunk-vendors.js',
                PATH_EXTENSIONS . '/kalender/tmpl/scripts/kalender/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => URL_ROOT."/rest.php/kalender",
                "acl" => ["read"=>1,"write"=>1,"delete"=>1],
                "apiKey" => DB::getGlobalSettings()->apiKey
            ]
        ];

    }
}