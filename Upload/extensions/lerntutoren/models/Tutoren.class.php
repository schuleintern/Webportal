<?php
/**
 *
 */


class extLerntutorenModelTutoren
{

    /**
     * @var data []
     */
    private $data = [];


    private $tutor = false;
    private $slots = false;


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
        return $this->data['tutorenID'];
    }

    public function getStatus() {
        return $this->data['status'];
    }

    public function getTimeCreated() {
        return $this->data['created'];
    }

    public function getFach() {
        return $this->data['fach'];
    }

    public function getJahrgang() {
        return $this->data['jahrgang'];
    }

    public function getEinheiten() {
        return $this->data['einheiten'];
    }

    public function getTutor() {
        if (!$this->tutor && $this->data['tutorenTutorAsvID']) {
            $this->tutor = user::getByAsvID($this->data['tutorenTutorAsvID']);
        }
        return $this->tutor;
    }

    public function getSlots() {
        if (!$this->slots && $this->getID() ) {
            $this->slots = $this->loadSlots( $this->getID() );
        }
        return $this->slots;
    }

    public function getSlotsCollection() {
        $slots = $this->getSlots();
        $collection = [];
        foreach ($slots as $item) {
            $collection[] = $item->getCollection();
        }
        return $collection;
    }

    public function getSlotsDiff() {
        $slots = $this->getSlots();
        $diff = 0;
        foreach ($slots as $item) {
            if ( !$item->isStatusAbort() ) {
                $diff += (int)$item->getEinheiten();
            }

        }
        return (int)$this->getEinheiten() - $diff;
    }


    private function loadSlots($id) {
        if ( !(int)$id ) {
            return false;
        }
        $items =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM tutoren_slots WHERE slotTutorenID = ".(int)$id );
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $items[] = new extLerntutorenModelSlot($data);
        }
        return $items;
    }

    public function setStatusOpen () {

        if ( $this->getID() ) {
            if ( DB::getDB()->query("UPDATE tutoren SET status='open' WHERE tutorenID=".$this->getID() ) ) {
                return true;
            }
        }
        return false;
    }

    public function setStatusClose () {

        if ( $this->getID() ) {
            if ( DB::getDB()->query("UPDATE tutoren SET status='closed' WHERE tutorenID=".$this->getID() ) ) {
                return true;
            }
        }
        return false;
    }



    /**
     * @return Array[]
     */
    public static function getAllByStatus($status = '') {

        $where = '';
        if ($status) {
            $where .= 'WHERE status = "'.$status.'"';
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM tutoren ".$where);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    /**
     * @param $tutor user
     * @return Array[]
     */
    public static function getAllByTutor(user $tutor)
    {
        $arr = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM tutoren WHERE tutorenTutorAsvID = '" . $tutor->getData('userAsvID') . "'");
        while ($data = DB::getDB()->fetch_array($dataSQL)) {
            $arr[] = new self($data);
        }
        return $arr;
    }

}