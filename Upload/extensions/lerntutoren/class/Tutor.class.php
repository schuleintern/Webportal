<?php

/**
 *
 */
class Tutor
{

    /**
     * @var Tutor[]
     */
    private static $all = [];

    private $data = [];

    /**
     * @var schueler
     */
    //private $schueler = null;

    /**
     * @var slots
     */
    private $slots = null;

    /**
     * @var tutor
     */
    private $tutor = null;

    /**
     * Lerntutor constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->tutor = user::getByAsvID($data['tutorenTutorAsvID']);
        /*if ($data['tutorenSchuelerAsvID']) {
            $this->schueler = user::getByAsvID($data['tutorenSchuelerAsvID']);
        }*/

        $this->slots = self::getSlotsByParent($this->data['tutorenID']);
    }

    /**
     * @return schueler|null
     */
    /*public function getSchueler()
    {
        return $this->schueler;
    }*/

    /**
     * @return schueler|null
     */
    public function getTutor()
    {
        return $this->tutor;
    }

    /**
     * @return array
     */
    public function getSlots()
    {
        return $this->slots;
    }




    /**
     * @return int
     */
    public function getID()
    {
        return $this->data['tutorenID'];
    }

    /**
     * @return string
     */
    public function getFach()
    {
        return $this->data['fach'];
    }

    /**
     * @return string
     */
    public function getJahrgang()
    {
        return $this->data['jahrgang'];
    }

    /**
     * @return string
     */
    public function getEinheiten()
    {
        return $this->data['einheiten'];
    }


    /**
     * @return array
     */
    public function getEinheitenDiff()
    {
        $slots = $this->getSlots();
        $reservedEinheiten = 0;
        for($j = 0; $j < sizeof($slots); $j++) {
            $reservedEinheiten += (int)$slots[$j]->getEinheiten();
        }
        $diff = $this->data['einheiten'] - $reservedEinheiten;
        return $diff;
    }


    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->data['status'];
    }

    /**
     * @return string
     */
    public function getStatusNice()
    {
        switch ($this->data['status']) {
            default:
                return '';
                break;
            case 'created':
                return '<div class="text-red"><i class="fas fa-unlock"></i> Erstellt<div>';
                break;
            case 'open':
                return '<div class="text-green"><i class="fa fa-user-check"></i> Offen</div>';
                break;
            case 'closed':
                return '<div class="text-red"><i class="fas fa-times-circle"></i> Beendet</div>';
                break;
        }
    }


    /**
     * Löschen
     */
    public function delete()
    {
        /*$slots = $this->getSlots();

        for($i = 0; $i < sizeof($slots); $i++) {
            $slots[$i]->delete();
        }*/

        DB::getDB()->query("DELETE FROM tutoren WHERE tutorenID='" . $this->getID() . "'");
    }

    /**
     * @return LerntutorSlot[]
     */
    /*public function getSlots() {
        return LerntutorSlot::getAllForLerntutor($this);
    }*/

    /**
     * @param $id
     * @return Lerntutor|null
     */
    public static function getByID($id)
    {
        $all = self::getAll();

        for ($i = 0; $i < sizeof($all); $i++) {
            if ($all[$i]->getID() == $id) {
                return $all[$i];
            }
        }

        return null;
    }

    /**
     * @param $schueler
     */
    /*public static function getBySchueler(schueler $schueler)
    {
        $all = self::getAll();

        for ($i = 0; $i < sizeof($all); $i++) {
            if ($all[$i]->getSchueler()->getAsvID() == $schueler->getAsvID()) {
                return $all[$i];
            }
        }

        return null;
    }*/

    /**
     * @param $schueler
     */
    public static function getByTutor(user $tutor)
    {
        $arr = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM tutoren WHERE tutorenTutorAsvID = '" . $tutor->getData('userAsvID') . "'");
        while ($data = DB::getDB()->fetch_array($dataSQL)) {
            $arr[] = new Tutor($data);
        }
        return $arr;
    }

    /**
     * @return Tutor[]
     */
    public static function getAll($order = '')
    {
        if (sizeof(self::$all) == 0) {

            $dataSQL = DB::getDB()->query("SELECT * FROM tutoren ".$order);

            while ($data = DB::getDB()->fetch_array($dataSQL)) {
                self::$all[] = new Tutor($data);
            }
        }

        return self::$all;
    }

    /**
     * @return Tutor[]
     */
    public static function getAllByStatus($status = '') {
        $all = self::getAll();
        $ret = [];
        foreach($all as $item) {
            if ($item->getStatus() == $status) {
                $ret[] = $item;
            }
        }
        return $ret;
    }


    /**
     * @return
     */
    public static function getSlotsByParent($tutorID)
    {
        $arr = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM tutoren_slots WHERE slotTutorenID = " . (int)$tutorID);

        while ($data = DB::getDB()->fetch_array($dataSQL)) {
            $arr[] = new TutorSlot($data);
        }

        return $arr;
    }

    /**
     * Neuen Lerntutor anlegen
     * @param schueler $schueler
     */
    /* public static function addTutor(schueler $schueler)
     {
         DB::getDB()->query("INSERT INTO tutoren (tutorenTutorAsvID) values('" . $schueler->getAsvID() . "')");
     }*/

}

