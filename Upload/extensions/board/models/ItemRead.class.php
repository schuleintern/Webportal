<?php

class extBoardModelItemRead extends ExtensionModel
{

    static $table = 'ext_board_item_read';

    static $fields = [
        'id',
        'item_id',
        'user_id'
    ];


    static $defaults = [
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, [
            'parent_id' => 'item_id'
        ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $files = false)
    {

        $collection = parent::getCollection();


        if ($full) {

        }

        return $collection;
    }


}
