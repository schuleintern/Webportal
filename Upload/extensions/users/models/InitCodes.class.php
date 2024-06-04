<?php

class extUsersModelInitCodes extends ExtensionModel
{

    static $table = 'initialpasswords';

    static $fields = [
        'initialPasswordID',
        'initialPasswordUserID',
        'initialPassword',
        'passwordPrinted'
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
                'table_id' => 'initialPasswordID',
                'parent_id' => 'initialPasswordUserID'
            ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();


        if ($full) {
            if ($this->getData('initialPasswordUserID')) {
                $collection['user'] = user::getCollectionByID($this->getData('initialPasswordUserID'));
                if ($collection['user']) {
                    $collection['userName'] = $collection['user']['name'];
                }
            }
            if ($this->getData('passwordPrinted')) {
                $collection['passwordPrintedTime'] = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::getMySQLDateFromUnixTimeStamp($this->getData('passwordPrinted')));
            }

        }

        if ( !$collection['user'] ) {
            return false;
        }


        return $collection;
    }



}
