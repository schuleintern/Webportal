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
     * @return Array
     */
    public static function isActive($uniqid = false) {

        if (!$uniqid) {
            return false;
        }
        $data = DB::getDB()->query_first("SELECT id, name, folder FROM extensions WHERE uniqid = '".$uniqid."' ", true);
        if ($data) {
            return $data;
        }
        return false;

    }

    /**
     * @return Array
     */
    public static function loadModules($folder = false) {

        if (!$folder) {
            return false;
        }

        $path = PATH_EXTENSIONS.$folder.DS.'models'.DS;
        if ( is_dir($path) ) {
            foreach(scandir($path) as $file) {
                if ($file != '.' && $file != '..') {
                    if (file_exists($path.$file)) {
                        include_once( $path.$file );
                    }
                }
            }
        }

        return false;

    }

    /**
     * @return Array
     */
    public static function getPages() {

        $all = [];
        $dataSQL = DB::getDB()->query("SELECT id, name, folder FROM extensions");
        while($data = DB::getDB()->fetch_array($dataSQL, true)) {

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