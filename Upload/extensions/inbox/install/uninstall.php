<?php

class extInboxUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_inbox_folders;');
        DB::getDB()->query('DROP TABLE ext_inbox_message;');
        DB::getDB()->query('DROP TABLE ext_inbox_message_body;');
        DB::getDB()->query('DROP TABLE ext_inbox_message_file;');
        DB::getDB()->query('DROP TABLE ext_inbox_user;');
        DB::getDB()->query('DROP TABLE ext_inboxs;');

        FILE::removeFolder(PATH_DATA.'ext_inbox');
    }

}
