<?php

/**
 *
 */
class ExtensionsPages {

    /**
     *  constructor.
     *
     */
    public function __construct() {

    }

    /**
     * @return Menues[]
     */
    public static function getPages() {

        $all = [];
        $dataSQL = DB::getDB()->query("SELECT id, name, folder FROM extensions");
        while($data = DB::getDB()->fetch_array($dataSQL)) {

            $data['submenu'] = [];

            if ( file_exists(PATH_EXTENSIONS.$data['folder'].'/extension.json') ) {
                $modulJSON = json_decode( file_get_contents(PATH_EXTENSIONS.$data['folder'].'/extension.json') );
                if ($modulJSON->submenu) {
                    $data['submenu'] = $modulJSON->submenu;
                }
            }

            $all[] = $data;

        }

/*        echo "<pre>";
        print_r($all);
        echo "</pre>";*/

        return $all;
    }

}