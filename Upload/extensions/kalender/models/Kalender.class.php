<?php
/**
 *
 */
class extKalenderModelKalender
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
    public function getTitle() {
        return $this->data['title'];
    }
    public function getState() {
        return $this->data['state'];
    }
    public function getColor() {
        return $this->data['color'];
    }
    public function getSort() {
        return $this->data['sort'];
    }
    public function getPreSelect() {
        return $this->data['preSelect'];
    }
    public function getAcl() {
        return $this->data['acl'];
    }
    public function getFerien() {
        return $this->data['ferien'];
    }
    public function getPublic() {
        return $this->data['public'];
    }

    public function getAdmins($full = false) {
        if ($this->data['admins']) {
            $ret = [];
            $list = json_decode($this->data['admins']);
            foreach ($list as $foo) {
                if ($full) {
                    if ($user = USER::getCollectionByID($foo, false, true)) {
                        $ret[] = $user;
                    }
                } else {
                    $ret[] = $foo;
                }

            }
            return $ret;
        }
        return [];
    }



    /**
     * Collection
     */

    public function getCollection($full = false, $userID = false, $withIcsfeed = false, $withAdmins = false) {

        $collection = [
            "id" => $this->getID(),
            "title" => $this->getTitle(),
            "state" => $this->getState(),
            "color" => $this->getColor(),
            "sort" => $this->getSort(),
            "preSelect" => $this->getPreSelect(),
            "aclID" => $this->getAcl(),
            "ferien" => $this->getFerien(),
            "public" => $this->getPublic()
        ];
        if ($full == true) {

            if ($collection['aclID']) {
                if ($userID) {
                    $user = user::getUserByID($userID);
                } else if (DB::getSession()) {
                    $user = DB::getSession()->getUser();
                }
                if ($user) {
                    $collection['acl'] = ACL::getAcl( $user, false, (int)$collection['aclID'] );
                } else {
                    $collection['acl'] = ACL::getBlank();
                }
            } else {
                $collection['acl'] = ACL::getBlank();
            }

        }
        if ($withIcsfeed == true) {
            $collection['icsfeed'] = $this->data['icsfeed'];
        }
        if ($withAdmins == true) {
            $collection['admins'] = $this->getAdmins(true);
        }

        return $collection;
    }



    /**
     * @return Array[]
     */
    public static function getAllAllowed($state = false, $public = false, $userID = false) {

        $kalenderDB = self::getAll($state);
        $kalenders = [];
        if (count($kalenderDB) > 0) {
            foreach ($kalenderDB as $item) {
                $arr = $item->getCollection(true, $userID);
                if ( (int)$arr['acl']['rights']['read'] === 1) {
                    if ($public) {
                        if ($arr['public'] == 1) {
                            $kalenders[] = $arr;
                        }
                    } else {
                        $kalenders[] = $arr;
                    }
                    
                } else if ($arr['public'] == 1) {
                    $kalenders[] = $arr;
                }
            }
        }
        return $kalenders;
    }



    /**
     * @return Array[]
     */
    public static function getAll($state = false) {

        /*
        $where = '';
        if ($state) {
            $where = 'WHERE state = '.(int)$state;
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_kalender as a
            $where
            ORDER BY a.sort ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
        */

        $ret =  [];
        if ($state) {
            $data = DB::run( 'SELECT * FROM ext_kalender WHERE state = :state ORDER BY sort', ["state" => (int)$state] )->fetchAll();
        } else {
            $data = DB::run( 'SELECT * FROM ext_kalender ORDER BY sort' )->fetchAll();
        }

        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }


    public static function getByID($id = false) {

        if (!$id) {
            return false;
        }

        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_kalender as a
            WHERE id = ".(int)$id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            return new self($data);
        }
        return false;
    }



    public static function updateSort($items)
    {

        if (!$items || count($items) < 1) {
            return false;
        }

        foreach ($items as $item) {

            if ( !DB::run( 'UPDATE ext_kalender SET sort = :state WHERE id = :id ',
                ["id" => (int)$item->id, "state" => (int)$item->sort] )) {
                return false;
            }

        }
        return true;

    }

    public static function updateAcl($id, $acl)
    {
        if ( DB::run( 'UPDATE ext_kalender SET acl = :acl WHERE id = :id ',
            ["id" => (int)$id, "acl" => (int)$acl] ) ) {
            return true;
        }
        return false;
    }

    public static function updateState($id, $state)
    {
        if ( DB::run( 'UPDATE ext_kalender SET state = :state WHERE id = :id ',
            ["id" => (int)$id, "state" => (int)$state] ) ) {
            return true;
        }
        return false;
    }

    public static function submitData($array) {

        if (!$array) {
            return false;
        }
        if (!$array['title']) {
            return false;
        }

        if ( !$array['state'] || (int)$array['state'] == 0) {
            $array['state'] = 0;
        }
        if ( !$array['color'] || $array['color'] == 'undefined' ) {
            $array['color'] = '';
        }
        if ( !$array['icsfeed'] || $array['icsfeed'] == 'undefined' || $array['icsfeed'] == 'null' ) {
            $array['icsfeed'] = '';
        }
        if ( !$array['admins'] || $array['admins'] == 'undefined' || $array['admins'] == 'null' ) {
            $array['admins'] = '';
        }

        $insert_id = 0;
        if ($array['id']) {

            // UPDATE !
            if (!DB::getDB()->query("UPDATE ext_kalender SET
                        title = '" . DB::getDB()->escapeString($array['title']) . "',
                        state = " . (int)DB::getDB()->escapeString($array['state']) . ",
                        color = '" . DB::getDB()->escapeString($array['color']) . "',
                        sort = " . (int)DB::getDB()->escapeString($array['sort']) . ",
                        preSelect = " . (int)DB::getDB()->escapeString($array['preSelect']) . ",
                        acl = " . (int)DB::getDB()->escapeString($array['acl']) . ",
                        ferien = " . (int)DB::getDB()->escapeString($array['ferien']) . ",
                        public = " . (int)DB::getDB()->escapeString($array['public']) . ",
                        icsfeed = '" . DB::getDB()->escapeString($array['icsfeed']) . "',
                        admins = '" . DB::getDB()->escapeString($array['admins']) . "'
                        WHERE id = " . (int)$array['id'])) {
                return false;
            }
            $insert_id = (int)$array['id'];

        } else {

            // INSERT !
            if ( !DB::getDB()->query("INSERT INTO ext_kalender
            (
                state,
                title,
                color,
                sort,
                preSelect,
                acl,
                ferien,
                public,
                icsfeed,
                admins
            ) values(
            1,
            '" .  DB::getDB()->escapeString($array['title']) . "',
            '" .  DB::getDB()->escapeString($array['color']) . "',
            " .  (int)DB::getDB()->escapeString($array['sort']) . ",
            " . (int)DB::getDB()->escapeString($array['preSelect']) . ",
            " . (int)DB::getDB()->escapeString($array['acl']) . ",
            " . (int)DB::getDB()->escapeString($array['ferien']) . ",
            " . (int)DB::getDB()->escapeString($array['public']) . ",
            '" .  DB::getDB()->escapeString($array['icsfeed']) . "',
            '" .  DB::getDB()->escapeString($array['admins']) . "'
            ) ") ) {
                return false;
            }
            $insert_id = DB::getDB()->insert_id();
        }



        return $insert_id;
    }

    public static function deleteFromID( $id ) {

        if (!$id) {
            return false;
        }

        if (!DB::getDB()->query("DELETE FROM ext_kalender WHERE id=".(int)$id)) {
            return false;
        }
        return true;

    }

    public static function deleteALL(  ) {

        if (!DB::getDB()->query("TRUNCATE TABLE ext_kalender")) {
            return false;
        }
        return true;
    }

    public static function countAll(  ) {
        if ($data = DB::getDB()->query_first("SELECT COUNT(id) AS count FROM ext_kalender; ")) {
            return $data['count'];
        }
        return true;
    }





}