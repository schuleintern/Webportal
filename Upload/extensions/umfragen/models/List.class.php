<?php

/**
 *
 */
class extUmfragenModelList extends ExtensionModel
{

    static $table = 'ext_umfragen_list';

    static $fields = [
        'id',
        'createdTime',
        'createdUserID',
        'state',
        'title',
        'userlist',
        'type'
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
            if ($collection['type'] == 'ext_inbox') {

                include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'MessageBody2.class.php';
                $MessageBodyClass = new extInboxModelMessageBody2();
                $body = $MessageBodyClass->getByUmfrage($collection['id']);
                $ret = [];
                if ($body) {
                    include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Message2.class.php';
                    $MessageClass = new extInboxModelMessage2();
                    $messages = $MessageClass->getMessagesByBody($body->getID());
                    if ($messages) {
                        foreach($messages as $message) {
                            if ($message->getData('folder_id') != 2) { // nicht aus den "gesendet" Ordner
                                $inbox_tmp = PAGE::getFactory()->getInboxByID($message->getData('inbox_id'));
                                $collection_tmp = $inbox_tmp->getCollection(true);

                                $ret[] = [
                                    'type' => 'inbox',
                                    'title' => $collection_tmp['title'],
                                    'inbox_id' => $collection_tmp['id'],
                                    'mid' => $message->getData('id'),
                                    'user' => $collection_tmp['user']

                                ];
                            }

                        }
                    }

                }
                $collection['userlist'] = $ret;
            } else {


                if ($collection['userlist'] && count($collection['userlist']) > 0) {
                    $ret = [];
                    foreach($collection['userlist'] as $foo) {

                        $user = user::getUserByID($foo);
                        if ($user) {
                            //$ret[] = $user->getCollection(true, true);

                            $ret[] = [
                                'type' => 'user',
                                'title' => $user->getDisplayName(),
                                'inbox_id' => false,
                                'mid' => $user->getUserID(),
                                'user' => $user->getCollection(true)
                            ];
                        }
                    }
                    $collection['userlist'] = $ret;
                }
            }
        }
        if ($showChilds) {
            // Childs
            $collection['childs'] = [];
            include_once PATH_EXTENSIONS . 'umfragen' . DS.'models' . DS .'Item.class.php';
            $sub = new extUmfragenModelItem();
            $childs = $sub->getByParentID($this->getID());
            if ($childs) {
                foreach($childs as $child) {
                    $collection['childs'][] = $child->getCollection();
                }
            }
        }

        


        return $collection;
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


    public function getMy($userID = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$userID ) {
            return false;
        }
        $ret = [];
        $userID = '%"'.$userID.'"%';
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE userlist LIKE :userID', ['userID' => $userID])->fetchAll();
        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }



    public function setListWithItems($data = false, $childs = false)
    {

        if (!$data || !$childs) {
            return false;
        }

        include_once PATH_EXTENSIONS .DS.'umfragen'.DS. 'models' . DS .'Item.class.php';
        $sub = new extUmfragenModelItem();

        $id = $data['id'];

        $userlist = [];
        foreach( $data['userlist'] as $foo) {
            $userlist[] = (string)$foo;
        }
        $data['userlist'] = json_encode($userlist);

        if ( $db = $this->save($data) ) {

            if (!$data['id']) {
                $id = $db->lastID;
            }

            if ($childs && $id) {
                $i = 1;
                foreach($childs as $child) {
                    if ($child->title) {
                        $sub->save([
                            'id' => $child->id,
                            'list_id' => $id,
                            'title' => $child->title,
                            'typ' => $child->typ,
                            'sort' => $i,
                            'createdTime' => $data['createdTime'],
                            'createdUserID' => $data['createdUserID']
                        ]);
                        $i++;
                    }

                }
            }
            return $id;
        }

        return false;
    }




}
