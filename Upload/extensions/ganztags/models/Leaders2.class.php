<?php

/**
 *
 */
class extGanztagsModelLeaders2 extends ExtensionModel
{

    static $table = 'ext_ganztags_leaders';

    static $fields = [
        'id',
        'user_id',
        'days',
        'info'
    ];


    static $defaults = [
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, ['parent_id' => 'user_id']);
        self::setModelFields(self::$fields, self::$defaults);
    }





    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($collection['days']) {
            $collection['days'] = json_decode($collection['days']);
        }

        if ($collection['user_id']) {
            $collection['user'] = user::getCollectionByID($collection['user_id']);
            $collection['userName'] = $collection['user']['name'];
        }

        if ($full) {

        }



        return $collection;
    }








}
