<?php

/**
 *
 */
class extFileshareModelItem extends ExtensionModel
{

    static $table = 'ext_fileshare_item';

    static $fields = [
        'id',
        'createdTime',
        'createdUserID',
        'state',
        'list_id',
        'title',
        'filename',
        'sort',
        'folder'
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
        parent::__construct($data, self::$table ? self::$table : false, ['parent_id' => 'list_id']);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getTitle()
    {
        return $this->getData('title');
    }
   

    public function getCollection($full = false, $url = false)
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



    public function getByFolderAndID($folderID = false, $fid = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$folderID || !$fid ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE id = :id AND folder = :folder  ', ['id' => $fid, 'folder' => $folderID])->fetch();
        if ($data) {
            return new self($data);
        }
        return $ret;
    }






}
