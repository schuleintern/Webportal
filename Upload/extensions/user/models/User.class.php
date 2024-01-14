<?php

/**
 *
 */
class extUserModelUser extends ExtensionModel
{

    static $table = 'users';

    static $fields = [
        'userID',
        'userName',
        'userEMail',
        'userSignature',
        'userAutoresponse',
        'userAutoresponseText'
    ];


    static $defaults = [
        'userID' => 0,
        'userName' => '',
        'userEMail' => '',
        'userSignature' => '',
        'userAutoresponse' => 0,
        'userAutoresponseText' => ''
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct(
            $data,
            self::$table ? self::$table : false,
            ['table_id' => 'userID']
        );
        self::setModelFields(self::$fields, self::$defaults);
    }



    public function getCollection()
    {

        $collection = parent::getCollection([]);

        if ($collection['userID']) {
            $user = User::getUserByID($collection['userID']);
            if ($user) {
                $collection['user'] = $user->getCollection(true, true);
            }
        }

        //$collection['userAutoresponse'] = (int)$collection['userAutoresponse']; // ? true : false;

        return $collection;
    }
}
