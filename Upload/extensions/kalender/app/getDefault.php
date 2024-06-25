<?php

class extKalenderAppGetDefault  {
	

    public function execute() {

        $url = DB::getGlobalSettings()->urlToIndexPHP;
        $url = str_replace('index.php','',$url);

        return [
            "scripts" => [
                PATH_EXTENSIONS . '/kalender/tmpl/scripts/kalender/dist/js/chunk-vendors.js',
                PATH_EXTENSIONS . '/kalender/tmpl/scripts/kalender/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => $url."rest.php/kalender",
                "acl" => ["read"=>1,"write"=>1,"delete"=>1],
                "apiKey" => DB::getGlobalSettings()->apiKey
            ]
        ];

    }
}