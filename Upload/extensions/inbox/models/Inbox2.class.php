<?php

/**
 *
 */
class extInboxModelInbox2 extends ExtensionModel
{

    static $table = 'ext_inboxs';

    static $fields = [
        'id',
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


    public function getCollection($full = false, $folder = false, $withString = false , $withCount = false)
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

                    $this->setValue('user_id', $temp_inboxUser->getData('user_id'));
                    $collection['user_id'] = $this->getData('user_id');
                    //$collection['userName'] = 'aaa';

                    $temp_user = user::getUserByID($temp_inboxUser->getData('user_id'));
                    if ($temp_user) {
                        $collection['user'] = $temp_user->getCollection(true, false, false, true);
                        $collection['title'] = $collection['user']['name'];
                        //$collection['user']['receiveEMail'] = $temp_user->receiveEMail();
                    }
                }
            }
            if ($this->getData('type') == 'group') {
                include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Users.class.php';
                $classUser = new extInboxModelUsers();
                $temp_inboxUser = $classUser->getByParentID($this->getID());
                
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
                if ($withCount) {
                    include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Message2.class.php';
                    $Message = new extInboxModelMessage2();
                }
                foreach ($folders_temp as $item) {
                    $folder = $item->getCollection(true);
                    if ($withCount) {
                        $messages = $Message->getUnreadMessages($collection['id'], $item->getID());
                        $folder['unread'] = count($messages);
                        if ($folder['id'] == 1) { // Posteingang
                            $collection['unread'] = $folder['unread'];
                        }
                    }
                    $folders[] = $folder;
                }
                $collection['folders'] = $folders;
            }
        }

        if ($withString) {
            if ($this->getData('type') == 'group') {
                $collection['str'] = '[{"typ":"inbox","content":"' . $this->getID() . '","inboxs":["' . $this->getID() . '"]}]';
                $collection['strLong'] = '[{"typ":"inbox","content":"' . $this->getID() . '","title":"' . $this->getData('title') . '","inboxs":[' . json_encode($collection) . ']}]';
            } else if ($this->getData('type') == 'user') {
                $collection['str'] = '[{"typ":"user","content":"' . $this->getData('user_id') . '","inboxs":["' . $this->getID() . '"]}]';
                $collection['strLong'] = '[{"typ":"user","content":"' . $this->getData('user_id') . '","title":"' . $collection['user']['name'] . '","inboxs":[' . json_encode($collection) . ']}]';
            }
        }

        if ($this->getData('user_id')) {
            $collection['user_id'] = $this->getData('user_id');
            if ($collection['user_id']) {
                $userTemp = user::getUserByID($collection['user_id']);
                if ($userTemp) {
                    $collection['user'] = $userTemp->getCollection(false,false,false,true);
                    $collection['userName'] = $collection['user']['name'];
                    $collection['user']['receiveEMail'] = $userTemp->receiveEMail();
                }
            }
            if (!$collection['userName']) {
                $collection['userName'] = '- leer -';
            }
        }

        // InboxUser
        if ($this->getData('timeOn')) {
            $collection['timeOn'] = $this->getData('timeOn');
        }
        if ($this->getData('timeOff')) {
            $collection['timeOff'] = $this->getData('timeOff');
        }
        if ($this->getData('isPublic')) {
            $collection['isPublic'] = $this->getData('isPublic');
        }


        return $collection;
    }


    public function getByUserID($userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT b.id, b.title, b.type, a.user_id FROM ext_inbox_user AS a
          LEFT JOIN ' . $this->getModelTable() . ' AS `b` ON a.inbox_id LIKE b.id
          WHERE a.user_id = :user_id  ORDER BY b.id  ', ['user_id' => $userID])->fetchAll();

        foreach ($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }

    public function getByUserIDFirst($userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT b.id, b.title, b.type, a.user_id FROM ext_inbox_user AS a
          LEFT JOIN ' . $this->getModelTable() . ' AS `b` ON a.inbox_id LIKE b.id
          WHERE a.user_id = :user_id  AND type = "user"  ', ['user_id' => $userID])->fetch();

        if ($data) {
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
        $data = DB::run('SELECT b.*, a.user_id, a.timeOn, a.timeOff, a.isPublic FROM ext_inbox_user AS a
          LEFT JOIN ' . $this->getModelTable() . ' AS `b` ON a.inbox_id LIKE b.id
          WHERE b.type = "user" ')->fetchAll();

        foreach ($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }


    public function getByTyp($typ = 'group')
    {

        $all = parent::getAll();
        $ret = [];
        foreach ($all as $item) {

            if ($item->getData('type') == $typ) {
                $ret[] = $item;
            }
        }
        return $ret;
    }


    public function isInboxFromUser($inbox_id = false, $userID = false)
    {

        if (!$inbox_id || !$userID) {
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

    public function syncUserGroups()
    {
        $data = DB::run('SELECT id, title, users FROM ext_userlist_groups ')->fetchAll();
        if ($data) {
            foreach ($data as $item) {


                if ($item['title'] && $item['id']) {

                    $data_inbox = DB::run("SELECT id, title, parent_id FROM ext_inboxs WHERE type = 'group' AND parent_id = :parent_id ", ['parent_id' => $item['id']])->fetch();
                    if (!$data_inbox) {
                        $db = DB::run("INSERT INTO ext_inboxs (title,type,parent_id) VALUES ('" . $item['title'] . "' , 'group', " . (int)$item['id'] . "); ");
                        $inbox_id = $db->lastID;
                    } else {
                        $inbox_id = $data_inbox['id'];
                    }

                    $users = json_decode($item['users']);
                    if ($users) {
                        foreach ($users as $user_id) {

                            $data_inbox_user = DB::run('SELECT a.id, b.title, b.type, a.user_id FROM ext_inbox_user AS a
                                  LEFT JOIN ext_inboxs AS `b` ON a.inbox_id LIKE b.id
                                  WHERE a.user_id = :user_id  AND b.type = "group" AND b.parent_id = :parent_id  ', ['user_id' => $user_id, 'parent_id' => $item['id']])->fetch();


                            if (!$data_inbox_user['id']) {
                                DB::run("INSERT INTO ext_inbox_user (user_id,inbox_id) VALUES (" . (int)$user_id . " , " . (int)$inbox_id . "); ");
                            }

                        }
                    }
                }
            }
        }

        $data_inbox_user = DB::run('SELECT a.id, b.title, b.type, a.user_id FROM ext_inbox_user AS a
                                  LEFT JOIN ext_inboxs AS `b` ON a.inbox_id LIKE b.id
                                  WHERE  b.type = "group" ')->fetchAll();

        foreach ($data_inbox_user as $user) {
            if ($user['user_id']) {
                $data = DB::run('SELECT id,title  FROM ext_userlist_groups WHERE users LIKE "%' . $user['user_id'] . '%" ')->fetch();
                if (!$data) {
                    DB::run('DELETE FROM ext_inbox_user WHERE id = :id ', ['id' => $user['id']] );
                }
            }

        }

        return true;
    }


}
