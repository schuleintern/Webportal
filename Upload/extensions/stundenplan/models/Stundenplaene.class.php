<?php

/**
 *
 */
class extStundenplanModelStundenplaene extends ExtensionModel
{

    static $table = 'stundenplan_plaene';

    static $fields = [
        'stundenplanID',
        'stundenplanAb',
        'stundenplanBis',
        'stundenplanUploadUserID',
        'stundenplanName',
        'stundenplanIsDeleted'
    ];


    static $defaults = [
        'stundenplanIsDeleted' => 0
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, [
            'table_id' => 'stundenplanID'
        ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getData('stundenplanUploadUserID')) {
                $temp_user = user::getUserByID($this->getData('stundenplanUploadUserID'));
                if ($temp_user) {
                    $collection['createdUser'] = $temp_user->getCollection();
                }
            }
        }

        return [
            "id" => $collection['stundenplanID'],
            "start" => $collection['stundenplanAb'],
            "end" => $collection['stundenplanBis'],
            "createdBy" => $collection['createdUser'],
            "title" => $collection['stundenplanName'],
            "isDeleted" => $collection['stundenplanIsDeleted']
        ];

        //return $collection;
    }


}
