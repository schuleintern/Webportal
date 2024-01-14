<?php

/**
 *
 */
class extFinanzenModelBuchung extends ExtensionModel
{

    static $table = 'ext_finanzen_buchung';

    static $fields = [
        'state',
        'createdTime',
        'createdUserID',
        'antrag_id',
        'user_id',
        'amount',
        'title',
        'quant'
    ];


    static $defaults = [
        'state' => 1,
        'antrag_id' => 0,
        'user_id' => 0
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false);
        self::setModelFields(self::$fields, self::$defaults);
    }



    public function getAntragID()
    {
        return $this->getData('antrag_id');
    }
    public function getUserID()
    {
        return $this->getData('user_id');
    }




    public function getCollection($full = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getUserID()) {
                $temp_user = user::getUserByID($this->getUserID());
                if ($temp_user) {
                    $collection['user'] = $temp_user->getCollection();
                }
            }
            if ($this->getAntragID()) {
                include_once PATH_EXTENSIONS . 'finanzen' . DS . 'models' . DS . 'Antrag.class.php';
                $class = new extFinanzenModelAntrag();
                $tmp_data = $class->getByID($this->getAntragID());

                if ($temp) {
                    $collection['antrag'] = $tmp_data->getCollection();
                }
            }
        }

        return $collection;
    }




    public function getMy($userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID ) {
            return false;
        }
        $ret = [];

        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE user_id = :userID', ['userID' => $userID])->fetchAll();
        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }




    /*
    public  function toRechnugen()
    {

        $buchungen = $this->getByState([1]);

        if (count($buchungen) < 1) {
            return false;
        }

        $users = [];

        foreach ($buchungen as $buchung) {
            if ($buchung->getUserID()) {
                if (!is_array($users[$buchung->getUserID()])) {
                    $users[$buchung->getUserID()] = [];
                }
                $users[$buchung->getUserID()][] = $buchung;
            }
        }

        include_once PATH_EXTENSION_ROOT . 'models' . DS . 'Rechnung.class.php';

        foreach ($users as $userID => $orders) {

            $class = new extFinanzenModelRechnung();

            if (!$class->makeRechnugen($userID, $orders)) {
                return false;
            }

            foreach ($orders as $row) {
                // state -> 2 = gebucht
                $row->setState(2);
            }
        }


        return false;
    }
    */
}
