<?php

/**
 *
 */
class extVplanModelList2 extends ExtensionModel
{

    static $table = 'ext_vplan_list';

    static $fields = [
        'id',
        'createdTime',
        'createdUser',
        'date',
        'klasse',
        'stunde',
        'user_alt',
        'user_neu',
        'fach_neu',
        'fach_alt',
        'raum_alt',
        'raum_neu',
        'info_1',
        'info_2',
        'info_3'
    ];

    
    static $defaults = [
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


    public function getCollection($full = false, $showUserlist = false, $showChilds = false)
    {

        $collection = parent::getCollection();

        if ($this->getData('date')) {
            $collection['date'] = DateFunctions::getNaturalDateFromMySQLDate($this->getData('date'));
        }

        if ($full) {
            if ($this->getCreatedUserID()) {
                $temp_user = user::getUserByID($this->getCreatedUserID());
                if ($temp_user) {
                    $collection['createdUser'] = $temp_user->getCollection();
                }
            }
        }

        return $collection;
    }


    public function getList($userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE createdUserID = :userID', ['userID' => $userID])->fetchAll();
        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
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
        $userID = '%"'.$userID.'"%';
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE userlist LIKE :userID', ['userID' => $userID])->fetchAll();
        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }






}
