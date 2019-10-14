<?php

/**
 *
 */
class Lerntutor {

    /**
     * @var Lerntutor[]
     */
    private static $all = [];

    private $data = [];

    /**
     * @var schueler
     */
    private $schueler = null;

    /**
     * Lerntutor constructor.
     * @param $data
     */
    public function __construct($data) {
        $this->data = $data;
        $this->schueler = schueler::getByAsvID($data['lerntutorSchuelerAsvID']);
    }

    /**
     * @return schueler|null
     */
    public function getSchueler() {
        return $this->schueler;
    }

    /**
     * @return int
     */
    public function getID() {
        return $this->data['lerntutorID'];
    }

    /**
     * Löschen
     */
    public function delete() {
        $slots = $this->getSlots();

        for($i = 0; $i < sizeof($slots); $i++) {
            $slots[$i]->delete();
        }

        DB::getDB()->query("DELETE FROM lerntutoren WHERE lerntutorID='" . $this->getID() . "'");
    }

    /**
     * @return LerntutorSlot[]
     */
    public function getSlots() {
        return LerntutorSlot::getAllForLerntutor($this);
    }

    /**
     * @param $id
     * @return Lerntutor|null
     */
    public static function getByID($id) {
        $all = self::getAll();

        for($i = 0; $i < sizeof($all); $i++) {
            if($all[$i]->getID() == $id) {
                return $all[$i];
            }
        }

        return null;
    }

    /**
     * @param $schueler
     */
    public static function getBySchueler(schueler $schueler) {
        $all = self::getAll();

        for($i = 0; $i < sizeof($all); $i++) {
            if($all[$i]->getSchueler()->getAsvID() == $schueler->getAsvID()) {
                return $all[$i];
            }
        }

        return null;
    }

    /**
     * @return Lerntutor[]
     */
    public static function getAll() {
        if(sizeof(self::$all) == 0) {
            $dataSQL = DB::getDB()->query("SELECT * FROM lerntutoren");

            while($data = DB::getDB()->fetch_array($dataSQL)) {
                self::$all[] = new Lerntutor($data);
            }
        }

        return self::$all;
    }

    /**
     * Neuen Lerntutor anlegen
     * @param schueler $schueler
     */
    public static function addLerntutor(schueler $schueler) {
        DB::getDB()->query("INSERT INTO lerntutoren (lerntutorSchuelerAsvID) values('" . $schueler->getAsvID() . "')");
    }

}