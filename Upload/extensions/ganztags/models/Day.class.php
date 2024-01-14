<?php

/**
 *
 */
class extGanztagsModelDay
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

    public function getDate()
    {
        return $this->data['date'];
    }

    public function getLeaderID()
    {
        return $this->data['leader_id'];
    }
    public function getGroupID()
    {
        return $this->data['group_id'];
    }

    public function getTitle()
    {
        return $this->data['title'];
    }
    public function getType()
    {
        return 'day-'.$this->data['type'];
    }

    public function getRoom()
    {
        return $this->data['room'];
    }

    public function getInfo()
    {
        return $this->data['info'];
    }

    public function getDuration()
    {
        return $this->data['duration'];
    }

    public function getColor()
    {
        return $this->data['color'];
    }

    public function getCreatedBy()
    {
        $user = USER::getUserByID($this->data['createdBy']);
        if ($user) {
            return $user->getCollection()['name'];
        }
        return  '';
    }
    public function getCreatedTime()
    {
        return $this->data['createdTime'];
    }


    public function getCollection($full = false)
    {
        $collection = [
            "id" => $this->getID(),
            "date" => $this->getDate(),
            "type" => $this->getType(),
            "leader_id" => $this->getLeaderID(),
            "group_id" => $this->getGroupID(),
            "title" => $this->getTitle(),
            "room" => $this->getRoom(),
            "color" => $this->getColor(),
            "info" => $this->getInfo(),
            "duration" => $this->getDuration(),
            "createdBy" => $this->getCreatedBy(),
            "createdTime" => $this->getCreatedTime()
        ];

        if ($collection['leader_id']) {

            include_once PATH_EXTENSION . 'models' . DS . 'Leaders.class.php';

            $leader = extGanztagsModelLeaders::getByID($collection['leader_id']);
            if ($leader) {
                $collection['leader'] = user::getUserByID($leader->getUserID())->getCollection();
            }

        }

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
    public function getSchueler($day = false) // day mo,di,mi,...
    {
        if (!$day) {
            return false;
        }
        include_once 'Schueler.class.php';

        $str = '"group":"'.$this->getGroupID().'"';
        $this->schueler[] = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_ganztags_schueler WHERE days LIKE '%$str%' ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $days = json_decode($data['days']);
            if ( $days->{$day} && $days->{$day}->group == $this->getGroupID() ) {
                $this->schueler[] = new extGanztagsModelSchueler($data, user::getUserByID($data['user_id']));
            }
        }
        return $this;
    }


    /**
     * @return Array[]
     */

    public static function getByDate($date = false)
    {
        if (!$date) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_ganztags_day WHERE date = '" . $date . "'");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    /**
     * @return Array[]
     */
    public static function setItem($item = false, $userID = false)
    {

        if (!$item) {
            return false;
        }
        if (!$item->leader_id) {
            $item->leader_id = 0;
        }
        if (!DB::getDB()->query("INSERT INTO ext_ganztags_day
            (
                type, date, leader_id, group_id, title, room, color, info, duration, createdBy, createdTime
            ) values(
                '" . DB::getDB()->escapeString($item->type) . "',
                '" . DB::getDB()->escapeString($item->date) . "',
                " . DB::getDB()->escapeString($item->leader_id) . ",
                " . DB::getDB()->escapeString($item->group_id) . ",
                '" . DB::getDB()->escapeString($item->title) . "',
                '" . DB::getDB()->escapeString($item->room) . "',
                '" . DB::getDB()->escapeString($item->color) . "',
                '" . DB::getDB()->escapeString($item->info) . "',
                " . DB::getDB()->escapeString($item->duration) . ",
                ".(int)$userID.",
                '".date("Y-m-d H:i:s",time())."'
            )
        ")) {
            return false;
        }

        return true;
    }

    /**
     * @return Array[]
     */
    public static function deleteItem($id = false)
    {

        if (!(int)$id) {
            return false;
        }

        if (!DB::getDB()->query("DELETE FROM ext_ganztags_day WHERE id = ".(int)$id)) {
            return false;
        }

        return true;
    }

}
