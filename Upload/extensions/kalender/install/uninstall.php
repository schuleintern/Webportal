<?php

class extKalenderUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_kalender;');
        DB::getDB()->query('DROP TABLE ext_kalender_events;');
        DB::getDB()->query('DROP TABLE ext_kalender_ics;');

        //FILE::removeFolder(PATH_DATA.'ext_import');
    }

}
