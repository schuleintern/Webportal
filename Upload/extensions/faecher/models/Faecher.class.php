<?php

class extFaecherModelFaecher extends ExtensionModel
{

    static $table = 'faecher';

    static $fields = [
        'fachID',
        'fachKurzform',
        'fachLangform',
        'fachIstSelbstErstellt',
        'fachASDID',
        'fachOrdnung'
    ];


    static $defaults = [
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false,
            [
                'table_id' => 'fachID'
            ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        return [
            'id' => $collection['fachID'],
            'short' => $collection['fachKurzform'],
            'long' => $collection['fachLangform'],
            'selbstErstellt' => $collection['fachIstSelbstErstellt'],
            'ordnung' => $collection['fachOrdnung'],
            'asdid' => $collection['fachASDID']
        ];
    }

}
