<?php

/**
 *
 */
class extInboxModelInbox2 extends ExtensionModel
{

    static $table = 'ext_inboxs';

    static $fields = [
        'title',
        'type',
        'createdTime',
        'createdUserID'
    ];

    
    static $defaults = [
        'type' => 'user'
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


    public function getCollection($full = false, $folder = false, $withString = false)
    {

        $collection = parent::getCollection();

        unset($collection['createdTime']);
        unset($collection['createdUserID']);
        unset($collection['state']);

        if ($full) {

            if ($this->getData('type') == 'user') {

                include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Users.class.php';
                $classUser = new extInboxModelUsers();
                $temp_inboxUser = $classUser->getByParentID($this->getID()) [0];
                if ($temp_inboxUser->getData('user_id')) {
                    $temp_user = user::getUserByID($temp_inboxUser->getData('user_id'));
                    if ($temp_user) {
                        $collection['user'] = $temp_user->getCollection(true);
                        $collection['title'] = $collection['user']['name'];
                    }
                }
            }

        }


        if ($folder) {
            include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Folder2.class.php';

            $classFolder = new extInboxModelFolder2();
            $folders_temp = $classFolder->getByParentID(0);
            $folders_user = $classFolder->getByParentID($this->getID());
            if ($folders_user) {
                $folders_temp = array_merge($folders_temp, $folders_user);
            }
            if ($folders_temp) {
                $folders = [];
                foreach ($folders_temp as $item) {
                    $folders[] = $item->getCollection(true);
                }
                $collection['folders'] = $folders;
            }
        }

        if ($withString) {
            if ($this->getData('type') == 'group') {

                $collection['str'] = '[{"typ":"group","content":"'.$this->getID().'","inboxs":["'.$this->getID().'"]}]';
                $collection['strLong'] = '[{"typ":"group","content":"'.$this->getID().'","inboxs":['.json_encode($collection).']}]';

            } else if ($this->getData('type') == 'user') {

                $collection['str'] = '[{"typ":"user","content":"'.$this->getData('user_id').'","inboxs":["'.$this->getID().'"]}]';
                $collection['strLong'] = '[{"typ":"user","content":"'.$this->getData('user_id').'","inboxs":['.json_encode($collection).']}]';

            }
        }

        if ( $this->getData('user_id') ) {
            $collection['user_id'] = $this->getData('user_id');
            if ($collection['user_id']) {
                $collection['user'] = user::getCollectionByID($collection['user_id']);
                $collection['userName'] = $collection['user']['name'];
            }
            if (!$collection['userName']) {
                $collection['userName'] = '- leer -';
            }

        }


        return $collection;
    }


    public function getByUserID($userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT b.id, b.title, b.type, a.user_id FROM ext_inbox_user AS a
          LEFT JOIN ' . $this->getModelTable() . ' AS `b` ON a.inbox_id LIKE b.id
          WHERE a.user_id = :user_id  ORDER BY b.title  ', ['user_id' => $userID])->fetchAll();

        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }

    public function getByUserIDFirst($userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT b.id, b.title, b.type, a.user_id FROM ext_inbox_user AS a
          LEFT JOIN ' . $this->getModelTable() . ' AS `b` ON a.inbox_id LIKE b.id
          WHERE a.user_id = :user_id  AND type = "user"  ', ['user_id' => $userID])->fetch();

        if($data) {
            return new self($data);
        }
        return false;
    }


    public function getByTypUser()
    {
        if (!self::$table) {
            return false;
        }

        $ret = [];
        $data = DB::run('SELECT b.*, a.user_id FROM ext_inbox_user AS a
          LEFT JOIN ' . $this->getModelTable() . ' AS `b` ON a.inbox_id LIKE b.id
          WHERE b.type = "user" ' )->fetchAll();

        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }


    public function getByTyp($typ = 'group') {

        $all = parent::getAll();
        $ret = [];
        foreach($all as $item) {
            
            if ($item->getData('type') == $typ) {
                $ret[] = $item;
            }
        }
        return $ret;
    }


    public function isInboxFromUser($inbox_id = false, $userID = false)
    {

        if (!$inbox_id || !$userID ) {
            return false;
        }

        $data = DB::run('SELECT a.id FROM ' . $this->getModelTable() . ' AS a
        LEFT JOIN ext_inbox_user AS b ON a.id = b.inbox_id
        WHERE a.id = :inbox_id  AND b.user_id = :user_id ', ['inbox_id' => $inbox_id, 'user_id' => $userID])->fetch();

        if ($data) {
            return new self($data);
        }
        return false;
    }


}
