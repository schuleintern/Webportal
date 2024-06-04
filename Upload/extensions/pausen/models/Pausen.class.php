<?php

class extPausenModelPausen extends ExtensionModel
{

    static $table = 'ext_pausen';

    static $fields = [
        'id',
        'createdUserID',
        'createdTime',
        'state',
        'title',
        'start',
        'end'
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


    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($collection['start']) {
            $collection['start'] = substr($collection['start'],0,5);
        }
        if ($collection['end']) {
            $collection['end'] = substr($collection['end'],0,5);
        }
        return $collection;
    }

}
