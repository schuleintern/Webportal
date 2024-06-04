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
        'quant',
        'orderNr'
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

        if ($collection['amount']) {
            $collection['amount'] = str_replace('.',',',$collection['amount']);
        }
        if ($collection['createdTime']) {
            $t = explode(' ', $collection['createdTime']);
            $t = explode('-', $t[0]);

            $collection['createdTime'] = $t[2].'.'.$t[1].'.'.$t[0];
        }


        if ($full) {
            if ($this->getData('createdUserID')) {
                $collection['createdUser'] = user::getCollectionByID($this->getData('createdUserID'));
                if ($collection['createdUser']) {
                    $collection['createdUserName'] = $collection['createdUser']['name'];
                }
            }
            if ($this->getData('user_id')) {
                $collection['user'] = user::getCollectionByID($this->getData('user_id'));
                if ($collection['user']) {
                    $collection['userName'] = $collection['user']['name'];
                }
            }
            /*
            if ($this->getAntragID()) {
                include_once PATH_EXTENSIONS . 'finanzen' . DS . 'models' . DS . 'Antrag.class.php';
                $class = new extFinanzenModelAntrag();
                $tmp_data = $class->getByID($this->getAntragID());

                if ($temp) {
                    $collection['antrag'] = $tmp_data->getCollection();
                }
            }
            */
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

    public static function getOrderNrPrefix()
    {
        $orderPrefix = DB::getSettings()->getValue('extFinanzen-ordernumber-prefix');
        if ($orderPrefix) {
            return $orderPrefix;
        }
        return 'si-';
    }

}
