<?php

class extAkteUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_akte_items;');
        DB::getDB()->query('DROP TABLE ext_akte_tags;');

        //FILE::removeFolder(PATH_DATA.'ext_import');
    }

}
