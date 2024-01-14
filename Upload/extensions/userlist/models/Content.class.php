<?php
/**
 *
 */
class extUserlistModelContent
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
    public function getMemberID() {
        return $this->data['member_id'];
    }
    public function getUserID() {
        return $this->data['user_id'];
    }
    public function getToggle() {
        return $this->data['toggle'];
    }
    public function getInfo() {
        return $this->data['info'];
    }


    public function getCollection($full = false) {

        $collection = [
            "id" => $this->getID(),
            "member_id" => $this->getMemberID(),
            "user_id" => $this->getUserID(),
            "toggle" => $this->getToggle(),
            "info" => $this->getInfo()
        ];

        if ($full) {
            $user = user::getUserByID($collection['user_id']);
            if ($user) {
                $user_collection = $user->getCollection();
                $collection['vorname'] = $user_collection['vorname'];
                $collection['nachname'] = $user_collection['nachname'];
                $collection['name'] = $user_collection['name'];
                $collection['type'] = $user_collection['type'];
            }

        }



        return $collection;
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

    /**
     * @return Array[]
     */
    public static function getMembersWithContentByTab($tab_id, $list_id) {


        if (!(int)$tab_id) {
            return false;
        }
        if (!(int)$list_id) {
            return false;
        }


        $contents =  [];
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_userlist_list_content as a
            WHERE a.tab_id =  ".(int)$tab_id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $contents[] = $data;
        }



        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT  a.id as `member_id`, a.user_id
            FROM ext_userlist_list_members as a
            WHERE a.list_id =  ".(int)$list_id);


        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {

            foreach($contents as $content) {
                if ($content['member_id'] == $data['member_id']) {
                    $data['toggle'] = $content['toggle'];
                    $data['info'] = $content['info'];
                    $data['id'] = $content['id'];
                }
            }
            $ret[] = new self($data);
        }
        return $ret;
    }






}