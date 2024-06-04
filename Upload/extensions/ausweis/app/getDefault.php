<?php

class extAusweisAppGetDefault
{


    public function execute()
    {

        return [
            "scripts" => [
                PATH_EXTENSIONS . '/ausweis/tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSIONS . '/ausweis/tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => str_replace('index','rest',DB::getGlobalSettings()->urlToIndexPHP)."/ausweis",
                "acl" => ["read" => 1, "write" => 1, "delete" => 1],
                "apiKey" => DB::getGlobalSettings()->apiKey,
                "selfURL" => DB::getGlobalSettings()->urlToIndexPHP.'?page=ext_ausweis&view=default'
            ]
        ];
    }
}
