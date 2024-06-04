<?php

/**
 *
 */
class extAdmintoolsModelChangeUserList extends ExtensionModel
{

    static $table = 'ext_admintools_changeuser_list';

    static $fields = [
        'id',
        'createdTime',
        'createdUserID',
        'state',
        'sort',
        'user_id'
    ];

    
    static $defaults = [
        'state' => 1
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



    public function getCollection($full = false, $showUserlist = false, $showChilds = false)
    {

        $collection = parent::getCollection();


        if ($full) {
            if ($this->getCreatedUserID()) {
                $temp_user = user::getUserByID($this->getCreatedUserID());
                if ($temp_user) {
                    $collection['createdUser'] = $temp_user->getCollection();
                }
            }

            if ($this->getData('user_id')) {
                $collection['user'] = user::getCollectionByID($this->getData('user_id'), true, true);
            }

        }

        return $collection;
    }






}
