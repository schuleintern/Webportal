<?php

/**
 *
 */
class extFileshareModelList extends ExtensionModel
{

    static $table = 'ext_fileshare_list';

    static $fields = [
        'id',
        'createdTime',
        'createdUserID',
        'state',
        'title',
        'userlist',
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
        parent::__construct($data, self::$table ? self::$table : false);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getTitle()
    {
        return $this->getData('title');
    }
    public function getUserlist()
    {
        return $this->getData('userlist');
    }

    static public function generateFolderName()
    {
        return substr(md5(rand()), 0, 16);
    }

    public function getCollection($full = false, $showUserlist = false, $showChilds = false)
    {

        $collection = parent::getCollection();

        if ( $this->getUserlist() ) {
            $collection['userlist'] = json_decode($this->getUserlist());
        }

        if ($full) {
            if ($this->getCreatedUserID()) {
                $temp_user = user::getUserByID($this->getCreatedUserID());
                if ($temp_user) {
                    $collection['createdUser'] = $temp_user->getCollection();
                }
            }
        }

        if ($showUserlist) {
            if ($collection['userlist']) {
                $ret = [];
                foreach($collection['userlist'] as $foo) {
                    $user = user::getUserByID($foo);
                    if ($user) {
                        $ret[] = $user->getCollection(true, true);
                    }
                    
                }
                $collection['userlist'] = $ret;
            }
        }
        if ($showChilds) {
            // Childs
            $collection['childs'] = [];
            include_once PATH_EXTENSIONS . 'fileshare' . DS.'models' . DS .'Item.class.php';
            $sub = new extFileshareModelItem();
            $childs = $sub->getByParentID($this->getID());
            if ($childs) {
                foreach($childs as $child) {
                    $collection['childs'][] = $child->getCollection();
                }
            }
        }

        


        return $collection;
    }


    public function getByUserlist($userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID ) {
            return false;
        }
        $ret = [];
        $userID = '%"'.$userID.'"%';
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE userlist LIKE :userID  ', ['userID' => $userID])->fetchAll();

        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }


    public function getList($userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE createdUserID = :userID', ['userID' => $userID])->fetchAll();
        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }


    public function getByFolderID($folderID = false)
    {

        if (!$folderID ) {
            return false;
        }
        $ret = false;
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE folder = :folderID', ['folderID' => $folderID])->fetch();
        if($data) {
            return new self($data);
        }
        return $ret;
    }





}
