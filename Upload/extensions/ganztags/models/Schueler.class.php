<?php

/**
 *
 */
class extGanztagsModelSchueler
{

    /**
     * @var data []
     */
    private $data = [];
    private $user = false;


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false, $user = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        if ($user) {
            $this->user = $user;
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

    public function getDays()
    {
        return $this->data['days'];
    }

    public function getInfo()
    {
        return $this->data['info'];
    }
    public function getAnz()
    {
        return $this->data['anz'];
    }

    public function getGroups()
    {
        return $this->data['groups'];
    }


    public function getCollection($full = false)
    {
        $days_arr = json_decode($this->getDays());
        $days = ['mo'=>$days_arr->mo,'di'=>$days_arr->di,'mi'=>$days_arr->mi,'do'=>$days_arr->do,'fr'=>$days_arr->fr,'sa'=>$days_arr->sa,'so'=>$days_arr->so];
        $collection = [
            "id" => $this->getID(),
            "user_id" => $this->getUserID(),
            "days" => $days,
            "info" => $this->getInfo(),
            "anz" => $this->getAnz(),
            "groups" => $this->getGroups()
        ];
        if ($this->user) {
            $userCollection = $this->user->getCollection(true);
            $collection['vorname'] = $userCollection['vorname'];
            $collection['nachname'] = $userCollection['nachname'];
            $collection['klasse'] = $userCollection['klasse'];
            $collection['gender'] = $userCollection['gender'];
            $collection['asvid'] = $userCollection['asvid'];
        } else {
            $collection['vorname'] = ' - ';
            $collection['nachname'] = ' - Ausgetreten - ';
        }
        return $collection;
    }


    /**
     * @return Array[]
     */
    public static function getUnsigned()
    {
        $ret = [];
        $all = self::getAll();

        $dataSQL = DB::getDB()->query("SELECT *  FROM schueler WHERE schuelerGanztagBetreuung != 0");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $found = false;
            foreach ($all as $item) {
                if ($item->getUserID() == (int)$data['schuelerUserID']) {
                    $found = true;
                }
            }
            if ($found === false) {
                $schueler = new schueler($data);
                if ($schueler->getUserID()) {
                    $ret[] = $schueler;
                }

            }
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getByID($id = false)
    {
        if (!$id) {
            return false;
        }

        $dataSQL = DB::getDB()->query_first("SELECT *  FROM ext_ganztags_schueler WHERE id = ".(int)$id);
        return new self($dataSQL, user::getUserByID($dataSQL['user_id']));

    }

    /**
     * @return Array[]
     */
    public static function getAll()
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_ganztags_schueler");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data, user::getUserByID($data['user_id']));
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function setAllUnsigned()
    {

        $all = self::getUnsigned();

        foreach ($all as $schueler) {

            if ($schueler->getUserID()) {
                DB::getDB()->query("INSERT INTO ext_ganztags_schueler
                    (
                        user_id,
                        days, info, anz
                    ) values(
                        " . $schueler->getUserID() . ",
                        '{}', NULL, NULL
                    )");
            }

        }
        return true;

    }


    /**
     * @return Array[]
     */
    public static function updateSchueler($id = false, $days = '{}', $info = '', $anz = 0)
    {

        if (!$id) {
            return false;
        }

        if (!DB::getDB()->query("UPDATE ext_ganztags_schueler SET
                        days = '" . DB::getDB()->escapeString($days) . "',
                        info = '" . DB::getDB()->escapeString($info) . "',
                        anz = " . DB::getDB()->escapeString($anz) . "
                        WHERE id = " . $id)) {
            return false;
        }
        return true;

    }


}