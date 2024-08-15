<?php

/**
 *
 */
class extUmfragenModelAnswer extends ExtensionModel
{

    static $table = 'ext_umfragen_answer';

    static $fields = [
        'id',
        'createdTime',
        'createdUserID',
        'list_id',
        'item_id',
        'content'
    ];

    
    static $defaults = [
        'content' => ''
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


    public function getByParentAndUserID($list_id = false, $userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID || !$list_id ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE createdUserID = :userID AND list_id = :list_id', ['userID' => $userID, 'list_id' => $list_id])->fetchAll();
        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }







}
