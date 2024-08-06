<?php

class extKlassenkalenderAppGetDefault  {
	

    public function execute() {

        $url = DB::getGlobalSettings()->urlToIndexPHP;
        $url = str_replace('index.php','',$url);

        return [
            "scripts" => [
                PATH_EXTENSIONS . '/klassenkalender/tmpl/scripts/kalender/dist/js/chunk-vendors.js',
                PATH_EXTENSIONS . '/klassenkalender/tmpl/scripts/kalender/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => $url."rest.php/klassenkalender",
                "acl" => ["read"=>1,"write"=>1,"delete"=>1],
                "apiKey" => DB::getGlobalSettings()->apiKey
            ]
        ];

    }
}