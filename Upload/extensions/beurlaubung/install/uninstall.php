<?php

class extBeurlaubungUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_beurlaubung_antrag;');

        //FILE::removeFolder(PATH_DATA.'ext_import');
    }

}
