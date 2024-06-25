<?php

/**
 *
 */
class extInboxModelMessageFile extends ExtensionModel
{

    static $table = 'ext_inbox_message_file';

    static $fields = [
        'id',
        'body_id',
        'file',
        'name',
        'uniqid'
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
            'parent_id' => 'body_id'
        ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $withText = false)
    {

        $collection = parent::getCollection();

        return $collection;
    }





    public function getByUniqidID($id = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$id) {
            return false;
        }
        $data = DB::run('SELECT * FROM '.$this->getModelTable().' WHERE uniqid = :uniqid  ', ['uniqid' => $id])->fetch();
        if ($data) {
            return new self($data);
        }
        return false;
    }





}
