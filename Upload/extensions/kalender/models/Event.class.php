<?php
/**
 *
 */
class extKalenderModelEvent
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
    public function getKalenderID() {
        return $this->data['kalender_id'];
    }
    public function getTitle() {
        if ($this->data['title']) {
            return urldecode( $this->data['title'] );
        }
        return '';
    }

    public function getDateStart() {
        return $this->data['dateStart'];
    }
    public function getTimeStart() {
        $arr = explode(':', $this->data['timeStart']);
        return $arr[0].':'.$arr[1];
    }
    public function getDateEnd() {
        return $this->data['dateEnd'];
    }
    public function getTimeEnd() {
        $arr = explode(':', $this->data['timeEnd']);
        return $arr[0].':'.$arr[1];
    }

    public function getPlace() {
        if ($this->data['place']) {
            return $this->data['place'] ;
        }
        return '';
    }
    public function getComment() {
        if ($this->data['comment']) {
            return urldecode( $this->data['comment'] );
        }
        return '';
    }
    public function getCreateUserID() {
        return $this->data['user_id'];
    }
    public function getCreateTime() {
        return $this->data['createdTime'];
    }
    public function getModifiedTime() {
        return $this->data['modifiedTime'];
    }
    public function getRepeatType() {
        return $this->data['repeat_type'];
    }



    /**
     * Collection
     */

    public function getCollection($full = false) {

        $collection = [
            "id" => $this->getID(),
            "calenderID" => $this->getKalenderID(),
            "title" => $this->getTitle(),
            "dateStart" => $this->getDateStart(),
            "timeStart" => $this->getTimeStart(),
            "dateEnd" => $this->getDateEnd(),
            "timeEnd" => $this->getTimeEnd(),
            "place" => $this->getPlace(),
            "comment" => $this->getComment(),
            "user_id" => $this->getCreateUserID(),
            "createdTime" => $this->getCreateTime(),
            "modifiedTime" => $this->getModifiedTime(),
            "repeat_type" => $this->getRepeatType(),

        ];
        if ($full == true) {

            if ($collection["user_id"]) {
                $user = User::getUserByID($collection["user_id"]);
                if ($user) {
                    $collection["user"] = $user->getCollection();
                } else {
                    $collection["user"] = ["name" => ""];
                }

            }
        }

        return $collection;
    }


    public static function getDayByKalender($date = false, $kalenderIDs = false) {

        if (!$date) {
            return false;
        }

        $where = [];
        foreach($kalenderIDs as $k_id) {
            $where[] = ' kalender_id = '.(int)$k_id;
        }
        $where = implode(' OR ',$where);

        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_kalender_events as a
            WHERE (".$where.") AND dateStart = '".$date."'
            ORDER BY a.dateStart ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;

    }


    /**
     * @return Array[]
     */
    public static function getAllByKalenderID($kalenderIDs = false) {

        if (!$kalenderIDs) {
            return false;
        }

        $where = [];
        foreach($kalenderIDs as $k_id) {
            $where[] = ' kalender_id = '.(int)$k_id;
        }
        $where = implode(' OR ',$where);

        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_kalender_events as a
            WHERE ".$where."
            ORDER BY a.dateStart ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    public static function submitData($array = false, $user_id = false) {

        if (!$array) {
            return false;
        }
        if (!$user_id) {
            return false;
        }
        if (!$array['title']) {
            return false;
        }
        if (!$array['kalender_id']) {
            return false;
        }

        $dateStart = DB::getDB()->escapeString($array['dateStart']);
        if ( !$dateStart ||$dateStart == '0000-00-00') {
            $dateStart = 'NULL';
        } else {
            $dateStart = "'".$dateStart."'";
        }
        $dateEnd = DB::getDB()->escapeString($array['dateEnd']);
        if ( !$dateEnd ||$dateEnd == '0000-00-00') {
            $dateEnd = 'NULL';
        } else {
            $dateEnd = "'".$dateEnd."'";
        }

        $insert_id = 0;
        if ($array['id']) {

            // UPDATE !
            if (!DB::getDB()->query("UPDATE ext_kalender_events SET
                        kalender_id = " . (int)DB::getDB()->escapeString($array['kalender_id']) . ",
                        title = '" . (string)DB::getDB()->escapeString($array['title']) . "',
                        dateStart = " .  $dateStart . ",
                        timeStart = '" . DB::getDB()->escapeString($array['timeStart']) . "',
                        dateEnd = " .  $dateEnd . ",
                        timeEnd = '" . DB::getDB()->escapeString($array['timeEnd']) . "',
                        place = '" . DB::getDB()->escapeString($array['place']) . "',
                        comment = '" . DB::getDB()->escapeString($array['comment']) . "',
                        repeat_type = '" . DB::getDB()->escapeString($array['repeat_type']) . "',
                        modifiedTime = '" . date('Y-m-d H-i:s', time()) . "'
                        WHERE id = " . (int)$array['id'])) {
                return false;
            }
            $insert_id = (int)$array['id'];

        } else {



            // INSERT !
            if ( !DB::getDB()->query("INSERT INTO ext_kalender_events
            (
                kalender_id,
                title,
                dateStart,
                timeStart,
                dateEnd,
                timeEnd,
                place,
                comment,
                repeat_type,
                user_id,
                createdTime
            ) values (
            " .  (int)DB::getDB()->escapeString($array['kalender_id']) . ",
            '" .  DB::getDB()->escapeString($array['title']) . "',
            " .  $dateStart . ",
            '" .  DB::getDB()->escapeString($array['timeStart']) . "',
            " .  $dateEnd . ",
            '" . DB::getDB()->escapeString($array['timeEnd']) . "',
            '" . DB::getDB()->escapeString($array['place']) . "',
            '" . DB::getDB()->escapeString($array['comment']) . "',
            '" . DB::getDB()->escapeString($array['repeat_type']) . "',
            " . $user_id . ",
            '" . date('Y-m-d H-i:s', time()) . "'
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

        if (!DB::getDB()->query("DELETE FROM ext_kalender_events WHERE id=".(int)$id)) {
            return false;
        }
        return true;

    }

    public static function deleteALL(  ) {

        if (!DB::getDB()->query("TRUNCATE TABLE ext_kalender_events")) {
            return false;
        }
        return true;
    }

    public static function countAll(  ) {
        if ($data = DB::getDB()->query_first("SELECT COUNT(id) AS count FROM ext_kalender_events; ")) {
            return $data['count'];
        }
        return true;
    }




}