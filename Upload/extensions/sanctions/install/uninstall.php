<?php

class extSanctionsUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_sanctions_count;');
        DB::getDB()->query('DROP TABLE ext_sanctions_users;');

        //FILE::removeFolder(PATH_DATA.'ext_import');
    }

}
