<?php

class extAkteModelItem extends ExtensionModel
{

    static $table = 'ext_akte_items';

    static $fields = [
        'id',
        'createdTime',
        'createdUserID',
        'state',
        'text',
        'user_id',
        'tags',
        'count'

    ];

    static $defaults = [];

    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, ['parent_id' => 'user_id']);
        self::setModelFields(self::$fields, self::$defaults);
    }
   

    public function getCollection($full = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getCreatedUserID()) {
                $temp_user = user::getUserByID($this->getCreatedUserID());
                if ($temp_user) {
                    $collection['createdUser'] = $temp_user->getCollection();
                }
            }

            if ($collection['tags']) {
                $collection['tags'] = json_decode($collection['tags']);
            }
        }

        return $collection;
    }








}
