<?php

/**
 *
 */
class extUmfragenModelItem extends ExtensionModel
{

    static $table = 'ext_umfragen_item';

    static $fields = [
        'id',
        'createdTime',
        'createdUserID',
        'state',
        'list_id',
        'title',
        'typ',
        'sort'
    ];

    
    static $defaults = [
        'state' => 1,
        'sort' => 1
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, ['parent_id' => 'list_id']);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getTitle()
    {
        return $this->getData('title');
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
