<?php
/**
 *
 */
class extSprechstundeModelDate
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
    public function getDate() {
        return $this->data['date'];
    }
    public function getSlotID() {
        return $this->data['slot_id'];
    }
    public function getInfo() {
        return $this->data['info'];
    }
    public function getMedium() {
        return $this->data['medium'];
    }
    public function getUserID() {
        return $this->data['user_id'];
    }
    public function getStatus() {
        return $this->data['status'];
    }

    public function getUser () {
        if (!$this->user && $this->data['user_id']) {
            $this->user = user::getUserByID($this->data['user_id']);
        }
        return $this->user;
    }

    public function getCollection() {
        $collection = [
            "id" => $this->getID(),
            "date" => $this->getDate(),
            "user_id" => $this->getUserID(),
            "slot_id" => $this->getSlotID(),
            "info" => $this->getInfo(),
            "medium" => $this->getMedium(),
            "user" => false,
            "status" => $this->getStatus()
        ];
        if ($this->getUser()) {
            $collection['user'] = $this->getUser()->getCollection();
        }
        return $collection;
    }


    /**
     * @return Array[]
     */
    public static function getAllByWeek($week_start = false) {


        $day_start = date('Y-m-d',(int)$week_start + (0 * 86400));
        $day_end = date('Y-m-d',(int)$week_start + (6 * 86400));

        $where = '';
        if ($day_start && $day_end) {
            $where .= 'WHERE date >=  DATE "'.$day_start.'" AND date <= DATE "'.$day_end.'"';
        } else {
            return false;
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_sprechstunde_dates ".$where);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getBySlotID($slot_id = false) {

        if (!(int)$slot_id) {
            return false;
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_sprechstunde_dates WHERE slot_id = ".(int)$slot_id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getByUserID($user_id = false) {

        if (!(int)$user_id) {
            return false;
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_sprechstunde_dates WHERE block = 0 AND user_id = ".(int)$user_id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    /**
     * @return Array[]
     */
    public static function getMyInFuture($user_id = false) {


        if (!(int)$user_id) {
            return false;
        }

        $today = date('Y-m-d',time());
        $count =  0;


        // Selbst gebuchte Dates
        $where = 'WHERE block = 0 AND status = 0 AND user_id = '.(int)$user_id.' AND date >=  DATE "'.$today.'"';
        $dataSQL = DB::getDB()->query("SELECT id FROM ext_sprechstunde_dates ".$where);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            if ($data['id']) {
                $count++;
            }
        }


        // meine Slots mit passenden Dates
        $where = 'WHERE user_id = '.(int)$user_id;
        $dataSQL = DB::getDB()->query("SELECT id FROM ext_sprechstunde_slots ".$where);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $where2 = 'WHERE block = 0 AND status = 0 AND id = '.(int)$data['id'].' AND date >=  DATE "'.$today.'"';
            $dataSQL2 = DB::getDB()->query("SELECT id FROM ext_sprechstunde_dates ".$where2);
            while ($data2 = DB::getDB()->fetch_array($dataSQL2, true)) {
                $count++;
            }
        }



        return $count;


    }

    /**
     * @return Array[]
     */
    public static function setStatus($id = false, $status = 0) {

        if (!(int)$id) {
            return false;
        }
        if (DB::getDB()->query("UPDATE ext_sprechstunde_dates
                SET status='" . DB::getDB()->escapeString($status) . "'
                WHERE id=".(int)$id
        )) {
            return true;
        }
        return false;
    }



}