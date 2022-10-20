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

    public function setItemsSort($items = false) {

        if ( !$items || !is_array($items) ) {
            return false;
        }
        foreach ($items as $item) {
            DB::getDB()->query("UPDATE menu_item SET 
                         sort=" . DB::getDB()->escapeString((int)$item->sort) . "
                         WHERE id=" . (int)$item->id );
        }
        return true;
    }

    public function removeItem($item_id = false) {
        if ( !(int)$item_id ) {
            return false;
        }
        if ( DB::getDB()->query(" DELETE FROM menu_item WHERE id=" . (int)$item_id ) ) {
            return true;
        }
        return false;

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
                            `active`,
                            `access`,
                            `options`,
                            `target`
							) VALUES (
								'".DB::getDB()->escapeString((string)$data['title'])."',
								'".DB::getDB()->escapeString((string)$data['icon'])."',
								".(int)DB::getDB()->escapeString((int)$data['menu_id']).",
								".(int)DB::getDB()->escapeString((int)$data['parent_id']).",
								'".DB::getDB()->escapeString((string)$data['params'])."',
								'".DB::getDB()->escapeString((string)$data['page'])."',
								".$active.",
								'".DB::getDB()->escapeString((string)$data['access'])."',
								'".DB::getDB()->escapeString((string)$data['options'])."',
								".DB::getDB()->escapeString((int)$data['target'])."
						);") ) {
                return true;
            }
        } else {
           if ( DB::getDB()->query("UPDATE menu_item SET 
                         title='" . DB::getDB()->escapeString((string)$data['title']) . "',
                         icon='" . DB::getDB()->escapeString($data['icon']) . "',
                         params='" . DB::getDB()->escapeString($data['params']) . "',
                         parent_id='" . (int)DB::getDB()->escapeString($data['parent_id']) . "',
                         page='" . DB::getDB()->escapeString($data['page']) . "',
                         access='" . DB::getDB()->escapeString($data['access']) . "',
                         options='" . DB::getDB()->escapeString($data['options']) . "',
                         target='" . (int)DB::getDB()->escapeString($data['target']) . "'
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

        $ret = [];
        $menueItemData = PAGE::getFactory()->getMenuItemByParentID( (int)$item_id, $active );
        if ($menueItemData) {
            foreach($menueItemData as $data) {
                $data['items'] = self::getNestedItems($data['id'], $active);
                $data['access'] = self::getAccess($data['access']);
                $ret[] = $data;
            }
        }

        /*
        $where = '';
        if ($active == true) {
            $where .= ' AND active = 1';
        }
        $dataSQL = DB::getDB()->query("SELECT * FROM menu_item WHERE id = ".(int)$item_id." ".$where);
        while($data = DB::getDB()->fetch_array($dataSQL)) {
            $data['items'] = self::getNestedItems($data['id'], $active);
            $ret[] = $data;
        }
        */
        return $ret;
    }

    /**
     * @return Menu[]
     */
    public static function getFromParentDeep($menu_id = false, $active = true) {

        if ( !(int)$menu_id ) {
            return false;
        }
        $ret = [];
        $menueItemData = PAGE::getFactory()->getMenuItemByMenuID( (int)(int)$menu_id, $active );
        if ($menueItemData) {
            foreach($menueItemData as $data) {
                $data['items'] = self::getNestedItems($data['id'], $active);
                $data['access'] = self::getAccess($data['access']);
                $ret[] = $data;
            }
        }

        /*
        $where = '';
        if ($active == true) {
            $where .= ' AND active = 1';
        }
        $dataSQL = DB::getDB()->query("SELECT * FROM menu_item WHERE menu_id = ".(int)$menu_id." ".$where." ORDER BY sort");
        while($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $data['items'] = self::getNestedItems($data['id'], $active);
            $data['access'] = self::getAccess($data['access']);
            $ret[] = $data;
        }
        */
        return $ret;
    }

    private static  function getNestedItems($parent_id, $active = true) {


        if ( !(int)$parent_id ) {
            return false;
        }


        $ret = [];
        $menueItemData = PAGE::getFactory()->getMenuItemByParentID( $parent_id, $active );
        if ($menueItemData) {
            foreach($menueItemData as $data) {
                $data['items'] = self::getNestedItems($data['id'], $active );
                $data['access'] = self::getAccess($data['access']);
                $ret[] = $data;
            }
        }

        /*
        $where = '';
        if ($active == true) {
            $where .= ' AND active = 1';
        }
        $dataChildSQL = DB::getDB()->query("SELECT * FROM menu_item WHERE parent_id = ".$parent_id." ".$where." ORDER BY sort");
        while($dataChild = DB::getDB()->fetch_array($dataChildSQL, true)) {

            $dataChild['items'] = self::getNestedItems($dataChild['id'], $active );
            $dataChild['access'] = self::getAccess($dataChild['access']);
            $ret[] = $dataChild;
        }
        */
        return $ret;

    }

    private static function getAccess($access) {
        if ($access) {
            $access = json_decode($access);
        } else {
            $access = [
                'admin' => 0,
                'adminGroup' => 0,
                'teacher' => 0,
                'pupil' => 0,
                'parents' => 0,
                'other' => 0
            ];
        }
        return $access;
    }




}