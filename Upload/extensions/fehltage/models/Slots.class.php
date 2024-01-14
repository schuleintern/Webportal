<?php

/**
 *
 */
class extFehltageModelSlots
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

    public function getInfo()
    {
        return $this->data['info'];
    }

    public function getCollection($full = false)
    {

        $collection = [
            "id" => $this->getID(),
            "tage" => $this->getTage(),
            "info" => $this->getInfo()
        ];


        if ($full) {

        }


        return $collection;
    }


    /**
     * @return Array[]
     */
    public static function getByID($id = 0)
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT a.* FROM ext_fehltage_slots AS a
			WHERE a.id = ".(int)$id." ORDER BY a.id");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getAll()
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT a.* FROM ext_fehltage_slots AS a  ORDER BY a.id");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function submit($data)
    {
        $data = (array)$data;

        if (!(int)$data['tage']) {
            return false;
        }

        if (!$data['id']) {

            if (!DB::getDB()->query("INSERT INTO ext_fehltage_slots
				(
					tage,
				    info
				) values(
					" . (int)DB::getDB()->escapeString($data['tage']) . ",
					'" . DB::getDB()->escapeString($data['info']) . "'
					
				)
		    ")) {
                return false;
            }

            return DB::getDB()->insert_id();

        } else {
            if (!DB::getDB()->query("UPDATE ext_fehltage_slots SET
                        tage = " . (int)DB::getDB()->escapeString($data['tage']) . ",
                        info = '" . DB::getDB()->escapeString($data['info']) . "'
                        WHERE id = " . (int)$data['id'])) {
                return false;
            }
            return $data['id'];
        }


        return false;
    }





}