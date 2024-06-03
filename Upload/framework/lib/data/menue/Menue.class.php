<?php

/**
 *
 */
class Menue {

    /**
     * @var all[]
     */
    private static $all = [];

    /**
     * @var data[]
     */
    private $data = [];

    /**
     * @var $items[]
     */
    private $items = [];

    /**
     *  constructor.
     *
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @return boolean
     */
    public static function setItem($data) {
        return MenueItems::setItem($data);
    }

    /**
     * @return boolean
     */
    public static function removeItem($item_id = false) {
        return MenueItems::removeItem($item_id);
    }

    /**
     * @return array
     */
    public function getCatsDeep($item_id = false) {
        if (!$item_id) {
            return false;
        }
        return MenueItems::getFromItemDeep($item_id);
    }

    /**
     * @return array
     */
    public function getItemsDeep($active = true) {

        if ( count($this->items) <= 0 ) {
            $this->items = MenueItems::getFromParentDeep($this->data['id'], $active);
        }
        return $this->items;
    }


    /**
     * @return Menues[]
     */
    public static function getAll() {
        if(sizeof(self::$all) == 0) {
            $dataSQL = DB::getDB()->query("SELECT id, title, alias FROM menu");
            while($data = DB::getDB()->fetch_array($dataSQL, true)) {
                self::$all[] = [
                    "id" => $data['id'],
                    "title" => $data['title'],
                    "alias" => $data['alias']
                ];
            }
        }
        return self::$all;
    }

    /**
     * @return Menu[]
     */
    public static function getFromAlias($alias) {
        if (!$alias) {
            return false;
        }
        $all = self::getAll();
        foreach($all as $data) {
            if ($data['alias'] == $alias) {
                return new Menue($data);
            }
        }
        return false;

    }

}