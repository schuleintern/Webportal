<?php
/**
 *
 */


class extRaumplanModelStunden
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
        return $this->data['stundeID'];
    }
    public function getKlasse() {
        return $this->data['stundeKlasse'];
    }
    public function getFach() {
        return $this->data['stundeFach'];
    }
    public function getRaum() {
        return $this->data['stundeRaum'];
    }
    public function getStunde() {
        return $this->data['stundeStunde'];
    }
    public function getDatum() {
        return $this->data['stundeDatum'];
    }
    public function getStundenplan() {
        return $this->data['stundenplanID'];
    }


    public function getCollection($full = false) {

        $datum =  date("d.m.Y", strtotime($this->getDatum()) );

        $collection = [
            "id" => $this->getID(),
            "klasse" => $this->getKlasse(),
            "fach" => $this->getFach(),
            "raum" => $this->getRaum(),
            "stunde" => $this->getStunde(),
            "datum" => $datum
        ];
        if ($full == true) {
            $collection['stundenplanID'] = $this->getStundenplan();
        }

        return $collection;
    }



    /**
     * @return Array[]
     */
    public static function getAllByUserDate($user_id = false, $date = false) {

        if (!$user_id || !$date) {
            return false;
        }
        $where = 'WHERE createdBy = '.$user_id.' AND stundeDatum = "'.$date.'"';
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM raumplan_stunden ".$where);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getAllByUserNext($user_id = false, $date = false) {

        if (!$user_id || !$date) {
            return false;
        }
        $where = 'WHERE createdBy = '.$user_id.' AND stundeDatum >= "'.$date.'" ORDER BY stundeDatum';
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM raumplan_stunden ".$where);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


}