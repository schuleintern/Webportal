<?php

class extStundenplanAppGetDefault extends AbstractApp
{

    public function execute()
    {

        return [
            "scripts" => [
                PATH_EXTENSIONS . '/stundenplan/tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSIONS . '/stundenplan/tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => URL_ROOT . "/rest.php/stundenplan",
                "acl" => ["read" => 1, "write" => 1, "delete" => 1],
                "apiKey" => DB::getGlobalSettings()->apiKey,
                "isMobile" => $this->isMobile
            ]
        ];

    }
}