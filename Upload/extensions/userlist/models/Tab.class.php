<?php
/**
 *
 */
class extUserlistModelTab
{

    /**
     * @var data []
     */
    private $data = [];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->setData($data);
    }

    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    /**
     * @return data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Getter
     */
    public function getID() {
        return $this->data['id'];
    }
    public function getListID() {
        return $this->data['list_id'];
    }
    public function getTitle() {
        return $this->data['title'];
    }


    public function getCollection($full = false) {

        $collection = [
            "id" => $this->getID(),
            "list_id" => $this->getListID(),
            "title" => $this->getTitle()
        ];

        return $collection;
    }



    /**
     * @return Array[]
     */
    public static function getAllByList($list_id) {

        if (!(int)$list_id) {
            return false;
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_userlist_list_tab as a
            WHERE a.list_id =  ".(int)$list_id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getByID($tab_id, $list_id) {

        if (!(int)$tab_id) {
            return false;
        }
        if (!(int)$list_id) {
            return false;
        }
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_userlist_list_tab as a
            WHERE a.id =  ".(int)$tab_id." AND a.list_id = ".(int)$list_id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            return new self($data);
        }
        return false;
    }






    /**
     * @return Array[]
     */
    public static function getContentByTab($tab_id) {


        if (!(int)$tab_id) {
            return false;
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_userlist_list_content as a
            WHERE a.tab_id =  ".(int)$tab_id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }




}