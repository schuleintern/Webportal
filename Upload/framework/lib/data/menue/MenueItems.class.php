<?php

/**
 *
 */
class MenueItems {


    /**
     *  constructor.
     */
    public function __construct() {

    }

    public static function setItem($data) {

        if ( !(int)$data['id'] ) {
            return false;
        }
        if ( !(string)$data['title'] ) {
            return false;
        }

        DB::getDB()->query("UPDATE menu_item SET 
                     title='" . DB::getDB()->escapeString($data['title']) . "',
                     icon='" . DB::getDB()->escapeString($data['icon']) . "'
                     WHERE id='" . (int)$data['id'] . "'");

        return true;

    }


    /**
     * @return Menu[]
     */
    public static function getFromItemDeep($item_id = false) {

        if ( !(int)$item_id ) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM menu_item WHERE id = ".(int)$item_id);
        while($data = DB::getDB()->fetch_array($dataSQL)) {
            $data['items'] = self::getNestedItems($data['id']);
            $ret[] = $data;
        }
        return $ret;
    }

    /**
     * @return Menu[]
     */
    public static function getFromParentDeep($menu_id = false) {

        if ( !(int)$menu_id ) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM menu_item WHERE menu_id = ".(int)$menu_id);
        while($data = DB::getDB()->fetch_array($dataSQL)) {
            $data['items'] = self::getNestedItems($data['id']);
            $ret[] = $data;
        }
        return $ret;
    }

    private static  function getNestedItems($parent_id) {

        if ( !(int)$parent_id ) {
            return false;
        }

        $ret = [];
        $dataChildSQL = DB::getDB()->query("SELECT * FROM menu_item WHERE parent_id = ".$parent_id);
        while($dataChild = DB::getDB()->fetch_array($dataChildSQL)) {

            $dataChild['items'] = self::getNestedItems($dataChild['id']);
            $ret[] = $dataChild;
        }
        return $ret;

    }




}