<?php

class extKlassenkalenderUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_klassenkalender;');
        DB::getDB()->query('DROP TABLE ext_klassenkalender_events;');
        DB::getDB()->query('DROP TABLE ext_klassenkalender_lnw;');

        //FILE::removeFolder(PATH_DATA.'ext_import');
    }

}
