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
        'content',
        'parent_id'
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

        if (!$collection['content']) {
            $collection['content'] = '';
        }

        return $collection;
    }


    public function getByIdAndParent($list_id = false, $parent_id = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$parent_id || !$list_id ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE parent_id = :parent_id AND list_id = :list_id', ['parent_id' => $parent_id, 'list_id' => $list_id])->fetchAll();
        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }

    public function getByParentAndUserID($list_id = false, $parent_id = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$parent_id || !$list_id ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE createdUserID = :parent_id AND list_id = :list_id', ['parent_id' => $parent_id, 'list_id' => $list_id])->fetchAll();
        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }







}
