<?php

/**
 *
 */
class extGanztagsModelLeaders
{

    /**
     * @var data []
     */
    private $data = [];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false, $user = false)
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

    public function getDays()
    {
        return json_decode($this->data['days']);
    }

    public function getInfo()
    {
        return $this->data['info'];
    }


    public function getCollection($full = false)
    {
        $collection = [
            "id" => $this->getID(),
            "user_id" => $this->getUserID(),
            "days" => $this->getDays(),
            "info" => $this->getInfo()
        ];

        if ( $this->getUserID() ) {
            $user = user::getUserByID($this->getUserID());
            if ($user) {
                $collection['user'] = $user->getCollection();
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
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_ganztags_leaders");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
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
        $dataSQL = DB::getDB()->query_first("SELECT *  FROM ext_ganztags_leaders WHERE id = ".(int)$id);
        return new self($dataSQL);
    }

    public static function getByUserID($id = false)
    {
        if (!$id) {
            return false;
        }
        $dataSQL = DB::getDB()->query_first("SELECT *  FROM ext_ganztags_leaders WHERE user_id = ".(int)$id);
        return new self($dataSQL);
    }


    /**
     * @return Array[]
     */
    public static function setAll($items = false)
    {

        if (!$items || count($items) < 1) {
            return false;
        }
        foreach ($items as $item) {

            $item->days = json_encode($item->days);
            if ( !$item->days || $item->days == '' || $item->days == 'null' ) {
                $item->days = '{}';
            }
            if ($item->id) {
                // UPDATE !
                if (!DB::getDB()->query("UPDATE ext_ganztags_leaders SET 
                                user_id = ".DB::getDB()->escapeString($item->user_id). ",
                                days = '".DB::getDB()->escapeString($item->days). "',
                                info = '".DB::getDB()->escapeString($item->info). "'
                                WHERE id = ".$item->id)) {
                    return false;
                }
            } else {
                // INSERT !
                if (!DB::getDB()->query("INSERT INTO ext_ganztags_leaders
                    (
                        user_id, days, info
                    ) values(
                        '" . DB::getDB()->escapeString($item->user_id) . "',
                        '" . DB::getDB()->escapeString($item->days) . "',
                        '" . DB::getDB()->escapeString($item->info) . "'
                    )
                ")) {
                    return false;
                }
            }

        }

        return true;
    }

}
