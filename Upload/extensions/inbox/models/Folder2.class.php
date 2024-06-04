<?php

/**
 *
 */
class extInboxModelFolder2 extends ExtensionModel
{

    static $table = 'ext_inbox_folders';

    static $fields = [
        'id',
        'title',
        'inbox_id',
        'sort'
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


    public function getCollection($full = false, $folder = false)
    {

        $collection = parent::getCollection();

        return $collection;
    }

}
