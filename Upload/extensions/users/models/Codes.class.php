<?php

class extUsersModelCodes extends ExtensionModel
{

    static $table = 'eltern_codes';

    static $fields = [
        'codeID',
        'codeSchuelerAsvID',
        'codeText',
        'codeUserID',
        'codePrinted'
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
                'table_id' => 'codeID',
                'parent_id' => 'codeUserID'
            ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getData('codeUserID')) {
                $arr = explode(',', $this->getData('codeUserID'));
                $collection['user'] = [];
                foreach ($arr as $foo) {
                    $collection['user'][] = user::getCollectionByID($foo);
                }

            }
            if ($this->getData('codeSchuelerAsvID')) {
                $user = user::getByASVID($this->getData('codeSchuelerAsvID'));
                if ($user) {
                    $collection['schuelerUser'] = $user->getCollection();
                    if ($collection['schuelerUser']) {
                        $collection['schuelerUserName'] = $collection['schuelerUser']['name'];
                    }
                }
            }
            if ($this->getData('codePrinted')) {
                $collection['codePrinted'] = DateFunctions::getNaturalDateFromMySQLDate(DateFunctions::getMySQLDateFromUnixTimeStamp($this->getData('codePrinted')));
            }
        }

        return $collection;
    }


}
