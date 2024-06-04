<?php

class extUsersModelGroups extends ExtensionModel
{

    static $table = 'ext_users_groups';

    static $fields = [
        'id',
        'createdTime',
        'createdBy',
        'state',
        'title',
        'users'
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


    public function getCollection($full = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getData('users')) {
                //$arr = explode(',', $this->getData('users'));
                $arr = json_decode($this->getData('users'));
                $collection['users'] = [];
                foreach ($arr as $foo) {
                    $collection['users'][] = user::getCollectionByID($foo);
                }

            }

        }

        return $collection;
    }


}
