<?php

/**
 *
 */
class LerntutorSlot {
    private $data = [];

    /**
     * @var schueler
     */
    private $schuelerBelegt = null;

    /**
     * Lerntutor constructor.
     * @param $data
     */
    public function __construct($data) {
        $this->data = $data;
        $this->schuelerBelegt = schueler::getByAsvID($data['slotSchuelerBelegt']);
    }

    /**
     * @return schueler|null
     */
    public function getSchuelerBelegt() {
        return $this->schuelerBelegt;
    }

    public function getFach() {
        return $this->data['slotFach'];
    }

    public function getJGS() {
        return $this->data['slotJahrgangsstufe'];
    }

    /**
     * @return int
     */
    public function getID() {
        return $this->data['slotID'];
    }

    /**
     * Löschen
     */
    public function delete() {
        DB::getDB()->query("DELETE FROM lerntutoren_slots WHERE slotID='" . $this->getID() . "'");
    }

    /**
     * @param schueler $schueler
     */
    public function assignSchueler($schueler) {
        if($schueler == null) {
            $schuelerAsvID = null;
            DB::getDB()->query("UPDATE lerntutoren_slots SET slotSchuelerBelegt=null WHERE slotID='" . $this->getID() . "'");
        }
        else {
            DB::getDB()->query("UPDATE lerntutoren_slots SET slotSchuelerBelegt='" . $schueler->getAsvID() . "' WHERE slotID='" . $this->getID() . "'");
        }
    }

    /**
     * @param Lerntutor $lerntutor
     * @return LerntutorSlot[]
     */
    public static function getAllForLerntutor(Lerntutor $lerntutor) {

        $all = [];

            $dataSQL = DB::getDB()->query("SELECT * FROM lerntutoren_slots WHERE slotLerntutorID='" . $lerntutor->getID() . "'");

            while($data = DB::getDB()->fetch_array($dataSQL)) {
                $all[] = new LerntutorSlot($data);
            }

        return $all;
    }

    /**
     * @param $id
     * @return LerntutorSlot|null
     */
    public static function getbyID($id) {
        $data = DB::getDB()->query_first("SELECT * FROM lerntutoren_slots WHERE slotID='" . intval($id) . "'");
        if($data['slotID'] > 0) return new LerntutorSlot($data);
        else return null;
    }

    public static function addSlotToLerntutor(Lerntutor $lerntutor, $fach, $jahrgangsstufe) {
        DB::getDB()->query("INSERT INTO lerntutoren_slots
                (
                    slotLerntutorID,
                    slotFach,
                    slotJahrgangsstufe
                )
                values(
                    '" . $lerntutor->getID() . "',
                    '" . DB::getDB()->escapeString($fach) ."',
                    '" . DB::getDB()->escapeString($jahrgangsstufe) ."'
                )");
    }

}