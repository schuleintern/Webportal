<?php

/**
 *
 */
class extSanctionsModelUser
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
    public function getData()
    {
        return $this->data;
    }


    /**
     * Getter
     */
    public function getID()
    {
        return $this->data['id'];
    }

    public function getUserID()
    {
        return $this->data['user_id'];
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function getCounts()
    {

        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_sanctions_count as a
            WHERE parent_id = " . $this->getID() . "
            ORDER BY a.count ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {

            if ($data['count']) {

                if ($data['createDate']) {
                    $data['createDate'] = date("d-m-Y H:i", strtotime($data['createDate']));
                }
                if ($data['createBy']) {
                    $user = User::getUserByID($data['createBy']);
                    if ($user) {
                        $data['createByUser'] = $user->getCollection();
                    }
                }
                if ($data['createUserID']) {
                    $user = User::getUserByID($data['createUserID']);
                    if ($user) {
                        $data['createUserID'] = $user->getCollection();
                    }
                }
                if ($data['doneDate']) {
                    $data['doneDate'] = date("d-m-Y H:i", strtotime($data['doneDate']));
                }
                if ($data['doneBy']) {
                    $user = User::getUserByID($data['doneBy']);
                    if ($user) {
                        $data['doneByUser'] = $user->getCollection();
                    }
                }
                if ($data['doneUser']) {
                    $user = User::getUserByID($data['doneUser']);
                    if ($user) {
                        $data['doneUser'] = $user->getCollection();
                    }
                }
                if ($data['doneUserID']) {
                    $user = User::getUserByID($data['doneUserID']);
                    if ($user) {
                        $data['doneUserID'] = $user->getCollection();
                    }
                }
                $ret[$data['count'] - 1] = $data;
            }
        }
        return $ret;

    }


    /**
     * Collection
     */

    public function getCollection($full = false)
    {

        $collection = [
            "id" => $this->getID(),
            "user_id" => $this->getUserID(),
            "status" => $this->getStatus()
        ];
        if ($full == true) {

            if ($collection["id"]) {
                $collection["counts"] = $this->getCounts();
            }
            if ($collection["user_id"]) {
                $user = User::getUserByID($collection["user_id"]);
                if ($user) {
                    $collection["user"] = $user->getCollection();
                    $collection["userName"] = $collection["user"]["name"];
                } else {
                    $collection["userName"] = "";
                }

            }
            if (!$collection["userName"]) {
                $collection["userName"] = "- ausgetreten -";
            }
            if (!$collection["user"]) {
                $collection["user"] = [
                    "name" => "- ausgetreten -"
                ];
            }
        }

        return $collection;
    }


    /**
     * @return Array[]
     */
    public static function getAll()
    {

        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_sanctions_users as a
            ORDER BY a.id ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getByUserID($userID)
    {
        if (!(int)$userID) {
            return false;
        }

        $dataSQL = DB::getDB()->query_first("SELECT  a.*
            FROM ext_sanctions_users as a
            WHERE a.user_id = ".(int)$userID);
        return new self($dataSQL);
    }



    public static function submitCount($array = false, $user_id = false)
    {
        if (!$array) {
            return false;
        }
        if (!$user_id) {
            return false;
        }
        if (!$array['id']) {
            return false;
        }

        $by_user_id = (int)DB::getDB()->escapeString($array['by']);

        if ($array['typ'] == 'finish') {

            if (DB::getDB()->query("UPDATE ext_sanctions_count
                SET doneInfo='" . (string)DB::getDB()->escapeString($array['info']) . "',
                doneBy=" . (int)$by_user_id . ",
                doneUserID=" . (int)$user_id . ",
                doneDate = '".date('Y-m-d H:i:s', time())."'
                WHERE id=".(int)$array['id']
            )) {
                return true;
            }

        } else {

            if (DB::getDB()->query("INSERT INTO ext_sanctions_count
            (
                parent_id,
                count,
                createDate,
                createInfo,
                createBy,
                createUserID
            ) values (
                 " . (int)DB::getDB()->escapeString($array['id']) . ",
                 " . (int)DB::getDB()->escapeString($array['count']) . ",
                 '" . date('Y-m-d H:i:s', time()) . "',
                '" . (string)DB::getDB()->escapeString($array['info']) . "',
                " . $by_user_id . ",
                " . $user_id . "
            ) ")) {
                return true;
            }

        }



        return false;

    }


    public static function deleteCount($id, $type, $user_id, $parent_id = false) {

        if (!$id) {
            return false;
        }
        if (!$type) {
            return false;
        }
        if (!$user_id) {
            return false;
        }

        if ($type == 'create') {

            if (DB::getDB()->query("UPDATE ext_sanctions_count
                SET createInfo=NULL,
                createBy=NULL,
                createUserID= NULL,
                createDate = NULL
                WHERE id=".(int)$id
            )) {
                return true;
            }

        } else if ($type == 'done') {

            if (DB::getDB()->query("UPDATE ext_sanctions_count
                SET doneInfo=NULL,
                doneBy=NULL,
                doneUserID= NULL,
                doneDate = NULL
                WHERE id=".(int)$id
            )) {
                return true;
            }

        } else if ($type == 'all') {

            if (DB::getDB()->query("DELETE FROM ext_sanctions_count WHERE id=".(int)$id)) {

                if ($parent_id) {
                    if (DB::getDB()->query("DELETE FROM ext_sanctions_users WHERE id=".(int)$parent_id)) {
                        return true;
                    }
                }

                return true;
            }

        }

        return false;


    }

    public static function submitData($array = false, $user_id = false)
    {

        if (!$array) {
            return false;
        }
        if (!$user_id) {
            return false;
        }
        if (!$array['user_id']) {
            return false;
        }


        // INSERT !
        if (!DB::getDB()->query("INSERT INTO ext_sanctions_users
            (
                user_id,
                status
            ) values (
            " . (int)DB::getDB()->escapeString($array['user_id']) . ",
            1
            ) ")) {
            return false;
        }
        $insert_id = DB::getDB()->insert_id();

        if ($insert_id) {


            $by_user_id = (int)DB::getDB()->escapeString($array['by']);

            if (!DB::getDB()->query("INSERT INTO ext_sanctions_count
            (
                parent_id,
                count,
                createDate,
                createInfo,
                createBy,
                createUserID
            ) values (
                 " . $insert_id . ",
                 1,
                 '" . date('Y-m-d H:i:s', time()) . "',
                '" . (string)DB::getDB()->escapeString($array['info']) . "',
                " . $by_user_id . ",
                " . $user_id . "
            ) ")) {
                return false;
            }

            return $insert_id;
        }

        return false;

    }




    public static function deleteALL()
    {

        if (!DB::getDB()->query("TRUNCATE TABLE ext_sanctions_users")) {
            return false;
        }
        return true;
    }

    public static function countAll()
    {
        if ($data = DB::getDB()->query_first("SELECT COUNT(id) AS count FROM ext_sanctions_users; ")) {
            return $data['count'];
        }
        return true;
    }


}