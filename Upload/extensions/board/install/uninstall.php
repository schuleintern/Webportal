<?php

class extBoardUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_board;');
        DB::getDB()->query('DROP TABLE ext_board_category;');
        DB::getDB()->query('DROP TABLE ext_board_item;');
        DB::getDB()->query('DROP TABLE ext_board_item_read;');

        FILE::removeFolder(PATH_DATA.'ext_board');
    }

}
