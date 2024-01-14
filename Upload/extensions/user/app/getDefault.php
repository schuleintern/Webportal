<?php

class extUserAppGetDefault
{


    public function execute()
    {

        
        return [
            "scripts" => [
                PATH_EXTENSIONS . '/user/tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSIONS . '/user/tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => str_replace('index','rest',DB::getGlobalSettings()->urlToIndexPHP)."/user",
                "acl" => ["read" => 1, "write" => 1, "delete" => 1],
                "apiKey" => DB::getGlobalSettings()->apiKey

            ]
        ];
    }
}
