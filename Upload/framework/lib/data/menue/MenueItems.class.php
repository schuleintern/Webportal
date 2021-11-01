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

    public static function setItemActive($data) {

        if ( !(string)$data['id'] ) {
            return false;
        }
        if ( DB::getDB()->query("UPDATE menu_item SET 
                         active=" . DB::getDB()->escapeString((int)$data['active']) . "
                         WHERE id=" . (int)$data['id'] ) ) {
            return true;
        }
        return false;
    }

    public static function setItem($data) {


        if ( !(string)$data['title'] ) {
            return false;
        }

        if ( !(int)$data['id'] ) {

            $active = DB::getDB()->escapeString((int)$data['active']);
            if (!$active) {
                $active = 0;
            }

            if ( DB::getDB()->query("INSERT INTO `menu_item` (
							`title`,
							`icon`,
							`menu_id`,
							`parent_id`,
							`params`,
							`page`,
                            `active`
							) VALUES (
								'".DB::getDB()->escapeString((string)$data['title'])."',
								'".DB::getDB()->escapeString((string)$data['icon'])."',
								".DB::getDB()->escapeString((int)$data['menu_id']).",
								".DB::getDB()->escapeString((int)$data['parent_id']).",
								'".DB::getDB()->escapeString((string)$data['params'])."',
								'".DB::getDB()->escapeString((string)$data['page'])."',
								".$active."
						);") ) {
                return true;
            }
        } else {
           if ( DB::getDB()->query("UPDATE menu_item SET 
                         title='" . DB::getDB()->escapeString((string)$data['title']) . "',
                         icon='" . DB::getDB()->escapeString($data['icon']) . "',
                         params='" . DB::getDB()->escapeString($data['params']) . "'
                         WHERE id='" . (int)$data['id'] . "'") ) {
               return true;
           }
        }
        return false;

    }


    /**
     * @return Menu[]
     */
    public static function getFromItemDeep($item_id = false, $active = true) {

        if ( !(int)$item_id ) {
            return false;
        }
        $where = '';
        if ($active == true) {
            $where .= ' AND active = 1';
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM menu_item WHERE id = ".(int)$item_id." ".$where);
        while($data = DB::getDB()->fetch_array($dataSQL)) {
            $data['items'] = self::getNestedItems($data['id'], $active);
            $ret[] = $data;
        }
        return $ret;
    }

    /**
     * @return Menu[]
     */
    public static function getFromParentDeep($menu_id = false, $active = true) {

        if ( !(int)$menu_id ) {
            return false;
        }
        $where = '';
        if ($active == true) {
            $where .= ' AND active = 1';
        }
        $ret = [];

        $dataSQL = DB::getDB()->query("SELECT * FROM menu_item WHERE menu_id = ".(int)$menu_id." ".$where);
        while($data = DB::getDB()->fetch_array($dataSQL)) {
            $data['items'] = self::getNestedItems($data['id'], $active);
            $ret[] = $data;
        }
        return $ret;
    }

    private static  function getNestedItems($parent_id, $active = true) {

        if ( !(int)$parent_id ) {
            return false;
        }
        $where = '';
        if ($active == true) {
            $where .= ' AND active = 1';
        }
        $ret = [];
        $dataChildSQL = DB::getDB()->query("SELECT * FROM menu_item WHERE parent_id = ".$parent_id." ".$where);
        while($dataChild = DB::getDB()->fetch_array($dataChildSQL)) {

            $dataChild['items'] = self::getNestedItems($dataChild['id']);
            $ret[] = $dataChild;
        }
        return $ret;

    }




}