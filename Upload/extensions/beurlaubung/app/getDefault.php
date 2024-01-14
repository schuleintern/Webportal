<?php

class extBeurlaubungAppGetDefault
{


    public function execute()
    {

        return [
            "scripts" => [
                PATH_EXTENSIONS . '/beurlaubung/tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSIONS . '/beurlaubung/tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "https://beta.schule-intern.de/rest.php/beurlaubung",
                "acl" => ["read" => 1, "write" => 1, "delete" => 1],


                "settings" => [],
                "maxStunden" => (int)$maxStunden,
                "stundenVormittag" => (int)$stundenVormittag,
                "stundenNachmittag" => (int)$stundenNachmittag,
                "mySchueler" => $mySchueler,
                "freigabeKL" => (int)$freigabeKL,
                "freigabeSL" => (int)$freigabeSL,
                "hinweisAntragOpen" => nl2br(DB::getSettings()->getValue("extBeurlaubung-antrag-open")),
                "hinweisAntragOpenFinish" => nl2br(DB::getSettings()->getValue("extBeurlaubung-antrag-finish"))

            ]
        ];
    }
}
