<?php

/**
 *
 */
class extGanztagsModelActivity
{

    /**
     * @var data []
     */
    private $data = [];

    /**
     * @var schueler []
     */
    private $schueler = [];

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


    public function getTitle()
    {
        return $this->data['title'];
    }
    public function getType()
    {
        return $this->data['type'];
    }

    public function getLeaderID()
    {
        return $this->data['leader_id'];
    }

    public function getRoom()
    {
        return $this->data['room'];
    }

    public function getColor()
    {
        return $this->data['color'];
    }

    public function getInfo()
    {
        return $this->data['info'];
    }

    public function getDuration()
    {
        return $this->data['duration'];
    }

    public function getDays()
    {
        return json_decode($this->data['days']);
    }


    public function getCollection($full = false)
    {
        $collection = [
            "id" => $this->getID(),
            "title" => $this->getTitle(),
            "type" => $this->getType(),
            "leader_id" => $this->getLeaderID(),
            "room" => $this->getRoom(),
            "color" => $this->getColor(),
            "info" => $this->getInfo(),
            "days" => $this->getDays(),
            "duration" => $this->getDuration()
        ];
        if ($full == true) {
            if ($this->schueler && count($this->schueler) > 0 ) {
                foreach ($this->schueler as $schueler) {
                    if ($schueler) {
                        if ($schueler->getID()) {
                            $collection['schueler'][] = $schueler->getCollection();
                        }
                    }
                }
            }
        }

        return $collection;
    }



    /**
     * @return Array[]
     */
    public function getSchueler()
    {
        include_once 'Schueler.class.php';

        $str = '"group":"'.$this->getID().'"';
        $this->schueler[] = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_ganztags_schueler WHERE days LIKE '%$str%' ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $this->schueler[] = new extGanztagsModelSchueler($data, user::getUserByID($data['user_id']));
        }
        return $this;
    }


    /**
     * @return Array[]
     */
    public static function getAll()
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_ganztags_groups WHERE type = 'activity' ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
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
            if (!$item->leader_id) {
                $item->leader_id = 0;
            }
            if (!$item->duration) {
                $item->duration = 0;
            }

            if ($item->id) {
                // UPDATE !
                if (!DB::getDB()->query("UPDATE ext_ganztags_groups SET
                        title = '".DB::getDB()->escapeString($item->title)."',
                        leader_id = ".DB::getDB()->escapeString($item->leader_id).",
                        room = '".DB::getDB()->escapeString($item->room)."',
                        color = '".DB::getDB()->escapeString($item->color)."',
                        info = '".DB::getDB()->escapeString($item->info)."',
                        days = '".DB::getDB()->escapeString($item->days)."',
                        duration = ".DB::getDB()->escapeString($item->duration)."
                        WHERE id = ".$item->id)) {
                    return false;
                }
            } else {
                // INSERT !
                if (!DB::getDB()->query("INSERT INTO ext_ganztags_groups
                    (
                        type, title, leader_id, room, color, info, days, duration
                    ) values(
                             'activity',
                        '" . DB::getDB()->escapeString($item->title) . "',
                        " . DB::getDB()->escapeString($item->leader_id) . ",
                        '" . DB::getDB()->escapeString($item->room) . "',
                        '" . DB::getDB()->escapeString($item->color) . "',
                        '" . DB::getDB()->escapeString($item->info ). "',
                        '" . DB::getDB()->escapeString($item->days ). "',
                        " . DB::getDB()->escapeString($item->duration) . "
                    )
                ")) {
                    return false;
                }
            }

        }

        return true;
    }

}
