<?php

class extUsersUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_users_groups;');

        //FILE::removeFolder(PATH_DATA.'ext_import');
    }

}
