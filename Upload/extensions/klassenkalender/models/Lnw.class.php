<?php

class extKlassenkalenderModelLnws extends ExtensionModel
{

    static $table = 'ext_klassenkalender_lnw';

    static $fields = [
        'id',
        'title',
        'short',
        'isPublic',
        'color'
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


    public function getCollection($full = false, $withAdmins = false)
    {
        $collection = parent::getCollection();

        if ($full) {


        }


        return $collection;
    }


    public function add($data = false)
    {

        if (!$data) {
            return false;
        }

        if ($this->save($data)) {
            return true;
        }
        return false;

    }


}