<?php

class extAusweisUninstall
{

    public static function uninstall()
    {

        DB::getDB()->query('DROP TABLE ext_ausweis_antrag;');
        DB::getDB()->query('DROP TABLE ext_ausweis_ausweis;');

        FILE::removeFolder(PATH_DATA.'ext_ausweis');
    }

}
