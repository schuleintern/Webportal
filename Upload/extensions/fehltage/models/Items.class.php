<?php

/**
 *
 */
class extFehltageModelItems
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

    public function getTage()
    {
        return $this->data['tage'];
    }

    public function getSlotID()
    {
        return $this->data['slot_id'];
    }

    public function getUserID()
    {
        return $this->data['user_id'];
    }

    public function getTotal()
    {
        return $this->data['total'];
    }

    public function getCollection($full = false)
    {

        $collection = [
            "id" => $this->getID(),
            "tage" => $this->getTage(),
            "slotID" => $this->getSlotID(),
            "userID" => $this->getUserID(),
            "total" => $this->getTotal()
        ];


        if ($full) {

            if ($collection['userID']) {
                $user = user::getUserByID($collection['userID']);
                if ($user) {
                    $collection['user'] = $user->getCollection();
                    $collection['username'] = $collection['user']['name'];
                }
            }
        }


        return $collection;
    }


    /**
     * @return Array[]
     */
    public static function getByID($id = 0)
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT a.* FROM ext_fehltage_items AS a
			WHERE a.id = ".(int)$id." ORDER BY a.id");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    public static function getByUserAndSlot($user_id = false, $slot_id = false)
    {

        if (!(int)$user_id) {
            return false;
        }
        if (!(int)$slot_id) {
            return false;
        }
        $where = ' WHERE user_id = '.(int)$user_id .' AND slot_id = '.(int)$slot_id;
        $data = DB::getDB()->query_first("SELECT * FROM ext_fehltage_items ".$where);
        if ($data) {
            return new self($data);
        }
        return false;
    }

    /**
     * @return Array[]
     */
    public static function getAll()
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT a.* FROM ext_fehltage_items AS a  ORDER BY a.id");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    public static function deleteALL(  ) {

        if (!DB::getDB()->query("TRUNCATE TABLE ext_fehltage_items")) {
            return false;
        }
        return true;
    }

    /**
     * @return Array[]
     */
    public static function submit($data)
    {
        $data = (array)$data;

        /*
        if (!(int)$data['tage']) {
            return false;
        }
        */

        if (!$data['id']) {

            if (!DB::getDB()->query("INSERT INTO ext_fehltage_items
				(
					tage,
				    slot_id,
				    user_id,
				    total
				) values(
					" . (int)DB::getDB()->escapeString($data['tage']) . ",
					" . (int)DB::getDB()->escapeString($data['slot_id']) . ",
					" . (int)DB::getDB()->escapeString($data['user_id']) . ",
					" . (int)DB::getDB()->escapeString($data['total']) . "
					
				)
		    ")) {
                return false;
            }

            return DB::getDB()->insert_id();

        } else {
            return $data['id'];
            /*
            if (!DB::getDB()->query("UPDATE ext_fehltage_items SET
                        tage = " . (int)DB::getDB()->escapeString($data['tage']) . ",
                        info = '" . DB::getDB()->escapeString($data['info']) . "'
                        WHERE id = " . (int)$data['id'])) {
                return false;
            }
            return $data['id'];
            */
        }


        return false;
    }





}