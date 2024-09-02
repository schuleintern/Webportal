<?php

class extAkteModelTags extends ExtensionModel
{

    static $table = 'ext_akte_tags';

    static $fields = [
        'id',
        'createdTime',
        'createdUserID',
        'state',
        'title'
    ];

    static $defaults = [];

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
            if ($this->getCreatedUserID()) {
                $temp_user = user::getUserByID($this->getCreatedUserID());
                if ($temp_user) {
                    $collection['createdUser'] = $temp_user->getCollection();
                }
            }
        }

        return $collection;
    }








}
