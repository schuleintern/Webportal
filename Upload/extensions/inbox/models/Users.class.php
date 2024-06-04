<?php

/**
 *
 */
class extInboxModelUsers extends ExtensionModel
{

    static $table = 'ext_inbox_user';

    static $fields = [
        'inbox_id',
        'user_id',
        'timeOn',
        'timeOff'
    ];

    
    static $defaults = [
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, ['parent_id' => 'inbox_id']);
        self::setModelFields(self::$fields, self::$defaults);
    }



    public function getCollection($full = false)
    {

        $collection = parent::getCollection();

        if ($full) {

            if ($collection['user_id']) {
                $user = User::getUserByID($collection['user_id']);
                if ($user) {
                    $ucoll = $user->getCollection();
                    if ($ucoll) {
                        $collection['user'] = $ucoll;
                    }
                }
            }
            
        }

        return $collection;
    }

/*
    public function getByInboxID ($id = false) {

        if (!$this->getModelTable()) {
            return false;
        }
        if (!$id) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE inbox_id = :id', ['id' => $id])->fetchAll();
        if ($data) {
            $class = get_called_class();
            foreach($data as $item) {
                $ret[] = new $class($item);
            }
            return $ret;
        }
        return false;
    }
    */





}
