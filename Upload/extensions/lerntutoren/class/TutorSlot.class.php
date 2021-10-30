<?php

/**
 *
 */
class TutorSlot
{

    private static $all = [];

    /**
     * @var TutorSlot[]
     */

    private $data = [];

    /**
     * @var schueler
     */
    private $schueler = null;

    /**
     * Lerntutor constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;

        $this->schueler = user::getByAsvID($data['slotSchuelerAsvID']);

    }

    /**
     * @return integer|null
     */
    public function getID()
    {
        return $this->data['slotID'];
    }

    /**
     * @return integer|null
     */
    public function getTutorenID()
    {
        return $this->data['slotTutorenID'];
    }

    /**
     * @return schueler|null
     */
    public function getSchueler()
    {
        return $this->schueler;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->data['slotStatus'];
    }

    /**
     * @return string
     */
    public function getStatusNice()
    {
        switch ($this->data['slotStatus']) {
            default:
                return '';
            case 'reserve':
                return '<div class="text-green"><i class="fas fa-check-circle"></i> Gebucht</div>';
                break;
            case 'close':
                return '<div class="text-red"><i class="fas fa-times-circle"></i> Beendet</div>';
                break;
        }
    }

    /**
     * @return string
     */
    public function getEinheiten()
    {
        return $this->data['slotEinheiten'];
    }

    /**
     * @return TutorSlot[]
     */
    public static function getAll()
    {
        if (sizeof(self::$all) == 0) {
            $dataSQL = DB::getDB()->query("SELECT * FROM tutoren_slots WHERE slotTutorenID = " . $this->data['slotTutorenID']);

            while ($data = DB::getDB()->fetch_array($dataSQL)) {
                self::$all[] = new Tutor($data);
            }
        }

        return self::$all;
    }


    /**
     * @param $id
     * @return TutorSlot|null
     */
    public static function getByID($id)
    {
        $dataSQL = DB::getDB()->query("SELECT * FROM tutoren_slots WHERE slotID = " . $id);

        while ($data = DB::getDB()->fetch_array($dataSQL)) {
            return  new TutorSlot($data);
        }
        return null;
    }

    /**
     * @param $schueler
     */
    public static function getByUser(user $user)
    {
        $arr = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM tutoren_slots WHERE slotSchuelerAsvID = '" . $user->getData('userAsvID') . "'");
        while ($data = DB::getDB()->fetch_array($dataSQL)) {
            $arr[] = new TutorSlot($data);
        }
        return $arr;
    }

}
