<?php

class extImportUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_import_asv_log;');

        //FILE::removeFolder(PATH_DATA.'ext_import');
    }

}
