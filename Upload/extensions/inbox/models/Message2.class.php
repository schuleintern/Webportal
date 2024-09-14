<?php

/**
 *
 */
class extInboxModelMessage2 extends ExtensionModel
{

    static $table = 'ext_inbox_message';

    static $fields = [
        'id',
        'inbox_id',
        'folder_id',
        'body_id',
        'isRead',
        'isReadUser',
        'isConfirm',
        'isEmail',
        'isAnswer',
        'isForward'
    ];

    
    static $defaults = [
        'isRead' => 0,
        'isEmail' => 0
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



    public function getCollection($full = false, $withText = false, $confirm = false, $files = false, $withInboxUser = false)
    {

        $collection = parent::getCollection();

        unset($collection['createdTime']);
        unset($collection['createdUserID']);
        unset($collection['state']);

        if ($this->getData('isConfirm') > 1) {
            $collection['confirmTime'] = date("d.m.Y H:i",$this->getData('isConfirm') );
        }

        if ($full) {

            include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'MessageBody2.class.php';
            $classBody = new extInboxModelMessageBody2();

            $body = $classBody->getByID( $this->getData('body_id') );
            $bodyCollection = $body->getCollection(true, $this->getData('id') );

            $collection['subject'] = $bodyCollection['subject'];


            $collection['from'] = false;
            $collection['to'] = false;


            $collection['from'] = $body->getSenderCollection();

            if ($collection['folder_id'] == 2) { // Postausgang

                if ($full === 'list') {
                    $collection['to'] = $body->getReceiversShort();
                } else {
                    $collection['to'] = $body->getReceiversShort(); // vll doch LONG ?
                }
            } else {
                if ($full === 'list') {

                } else {
                    $collection['to'] = $body->getReceiversLong();
                }
            }

            $collection['toCC'] = $body->getReceiversCC();
            $collection['priority'] = $bodyCollection['priority'];
            $collection['isPrivat'] = $bodyCollection['isPrivat'];
            $collection['noAnswer'] = $bodyCollection['noAnswer'];
            $collection['files'] = $bodyCollection['files'];
            $collection['umfrage'] = $bodyCollection['umfrage'];
            $collection['umfragen'] = $bodyCollection['umfragen'];
            $collection['date'] = date("d.m.Y H:i", strtotime($bodyCollection['createdTime']) );


            if ($collection['isRead']) {
                $collection['isReadDate'] = date('d.m.Y H:i',$collection['isRead']);
            }
            if ($collection['isReadUser']) {
                $collection['isReadUser'] = User::getCollectionByID($collection['isReadUser']);
            }
            if ($withText) {
                $collection['text'] = nl2br($bodyCollection['text']);
            }
            if ($full !== 'list') {
                if ($collection['isAnswer']) {
                    $collection['isAnswer'] = date('d.m.Y H:i', $collection['isAnswer']);
                }
                if ($collection['isForward']) {
                    $collection['isForward'] = date('d.m.Y H:i', $collection['isForward']);
                }

                include_once PATH_EXTENSION . 'models' . DS . 'MessageIsRead.class.php';
                $MessageIsRead = new extInboxModelMessageIsRead();
                $isReads = $MessageIsRead->getByParentID($collection['id']);
                if ($isReads) {
                    $collection['isReadList'] = [];
                    foreach ($isReads as $isRead) {
                        $collection['isReadList'][] = $isRead->getCollection();
                    }
                }

            }
        }

        if ($files) {

            /*
            if ($collection['files']) {
                if (EXTENSION::isActive('ext.zwiebelgasse.fileshare')) {
                    include_once PATH_EXTENSIONS . 'fileshare' . DS . 'models' . DS . 'List.class.php';
                    $FileShare = new extFileshareModelList();
                    $fileshare = $FileShare->getByFolderID($collection['files']);
                    if ($fileshare) {
                        $collection['filesFolder'] = $fileshare->getCollection(false, false, true);
                    }
                }
            }
            */

            if ($bodyCollection['id'] && $bodyCollection['files']) {

                include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'MessageFile.class.php';
                $filesBody = new extInboxModelMessageFile();
                $tmpFiles = $filesBody->getByParentID($bodyCollection['id']);
                if ($tmpFiles) {
                    $retFiles = [];
                    foreach ($tmpFiles as $tmpFile) {
                        $retFiles[] = $tmpFile->getCollection();
                    }
                }
                $collection['files'] = $retFiles;

            }

        }

        if ($confirm) {

            if ($this->getData('isConfirm') == 1) {


                $collection['confirmList'] = [
                    'to' => [],
                    'toCC' => [],
                    'toBCC' => []
                ];


                $arr = $body->getReceiversLong();
                foreach ($arr as $item) {

                    if ($item['title'] && $item['inbox']) {

                        $foo = [
                            "title" => $item['title'],
                            "inboxs" => []
                        ];

                        foreach($item['inbox'] as $inbox) {
                            $msg_temp = $this->getMessageByInboxBody( $inbox['id'], $this->getData('body_id') );
                            $inbox_tmp = PAGE::getFactory()->getInboxByID($inbox['id']);
                            if ($msg_temp && $inbox_tmp) {
                                $foo['inboxs'][] =  [
                                    'inbox' => $inbox_tmp->getCollection(true),
                                    'msg' => $msg_temp->getCollection()
                                ];
                            }
                        }
                        $collection['confirmList']['to'][] = $foo;
                    }
                }
            }

        }

        if ($withInboxUser) {

            $inbox_tmp = PAGE::getFactory()->getInboxByID($this->getData('inbox_id'));
            if ($inbox_tmp) {
                $collection['inbox'] = $inbox_tmp->getCollection(true);
            }
        }


        return $collection;
    }


    public function getMessages($inbox_id = false, $folder_id = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$inbox_id || !$folder_id ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE inbox_id = :inbox_id AND folder_id = :folder_id ORDER BY id DESC ', ['inbox_id' => $inbox_id, 'folder_id' => $folder_id])->fetchAll();

        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }

    public  function getMessageByInboxBody($inbox_id = false, $body_id = false)
    {
        if (!(int)$inbox_id || !(int)$body_id) {
            return false;
        }
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE inbox_id = :inbox_id AND body_id = :body_id  ', ['inbox_id' => $inbox_id, 'body_id' => $body_id])->fetch();
        if ($data) {
            return new self($data);
        }
        return false;
    }


    public  function getMessagesByBody($body_id = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!(int)$body_id) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE  body_id = :body_id  ', ['body_id' => $body_id])->fetchAll();
        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }

    public function getUnreadMessages($inbox_id = false, $folder_id = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$inbox_id || !$folder_id ) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE inbox_id = :inbox_id AND folder_id = :folder_id AND (isRead = 0 OR isRead = 1) ORDER BY id DESC', ['inbox_id' => $inbox_id, 'folder_id' => $folder_id])->fetchAll();

        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }

    public function getAllUnreadMessages()
    {
        if (!self::$table) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE folder_id = 1 AND (isRead = 0 OR isRead = 1) ORDER BY id ')->fetchAll();

        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }

    public function getAllUnreadUnsendMessages()
    {
        if (!self::$table) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE folder_id = 1 AND (isRead = 0 OR isRead = 1) AND isEmail = 0 ORDER BY id DESC ')->fetchAll();

        foreach($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }



    public function sendMessage($data = false)
    {
        if (!$data) {
            return false;
        }

        $sender_id = (int)$data['sender_id'];
        if (!$sender_id) {
            return false;
        }

        /*
        $receivers_array = explode(',', $receiver);
        if (count($receivers_array) <= 0) {
            return false;
        }
        */

        $send = true;
        $folder_id_in = 1;
        $folder_id_out = 2;

        if ($data['folderID']) {
            if ($data['folderID'] == 4) { // Entwurf
                $send = false;
                $folder_id_out = 4;
            }
        }

        if ($send) {
            if (!$data['receiver'] || $data['receiver'] == '') {
                return false;
            }
            $receivers_array = json_decode($data['receiver'], true);
            if ( !$receivers_array || count($receivers_array) < 1 ) {
                return false;
            }
        }



        $subject = DB::getDB()->escapeString(trim((string)$data['subject']));
        $text = DB::getDB()->escapeString(trim((string)$data['text']));

        //$createTime = date('Y-m-d H:i', time());

        if (!$data['createTime']) {
            $data['createTime'] = date('Y-m-d H:i', time());;
        }

        $files = 0;

        include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'MessageBody2.class.php';
        $bodyClass = new extInboxModelMessageBody2();
        $body = $bodyClass->save([
            'sender' => $data['sender_id'],
            'receivers' => (string)$data['receiver'],
            'receivers_cc' => $data['receivers_cc'],
            'createdTime' => $data['createTime'],
            'subject' =>$data['subject'],
            'text' => $data['text'],
            'priority' => $data['priority'],
            'isPrivat' => $data['isPrivat'],
            'noAnswer' => $data['noAnswer'],
            'files' => (int)$data['files']
        ]);



        //$body_id = extInboxModelMessageBody::setDatabase($data['sender_id'], (string)$data['receiver'], $receiver_cc, $createTime, $subject, $text, $priority, $files);

        if ( $body && $body->lastID ) {

            if ($data['files']) {
                $filesArr = json_decode($_POST['files']);
                if ($filesArr) {
                    include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'MessageFile.class.php';
                    $fileClass = new extInboxModelMessageFile();
                    foreach ($filesArr as $file) {
                        if ($file->path && $file->name) {
                            $tempFile = $bodyClass->uploadMove($file->path, $body->lastID);
                            if ($tempFile) {
                                $fileClass->save([
                                    'body_id' => $body->lastID,
                                    'file' => $tempFile,
                                    'name' => $file->name,
                                    'uniqid' => uniqid()
                                ]);
                            }
                        }
                    }
                    $bodyClass->update([
                        'id' => $body->lastID,
                        'files' => true
                    ]);
                }
            }


            include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Inbox2.class.php';
            $InboxClass = new extInboxModelInbox2();

            $userlist = [];
            $inboxlist = [];

            // POSTEINGANG
            if ($send) {
                foreach ($receivers_array as $inbox_group) {
                    if ($inbox_group['inboxs']) {
                        foreach ($inbox_group['inboxs'] as $item) {

                            // Inbox to UserIds
                            $inbox = $InboxClass->getByID($item);
                            if ($inbox) {
                                $inboxTemp = $inbox->getCollection(true);

                                if ($inboxTemp['user_id']) {
                                    $userlist[] = $inboxTemp['user_id'];
                                }
                            }
                            if (!in_array($item, $inboxlist)) {
                                $inboxlist[] = $item;
                            }

                            if ( !$this->save([
                                'inbox_id' => $item,
                                'folder_id' => $folder_id_in,
                                'body_id' => $body->lastID,
                                'isConfirm' => $data['confirm']
                            ])) {
                                return false;
                            }



                        }
                    }
                }
            }




            // POSTEINGANG CC
            if ($send && $data['receivers_cc']) {
                $receivers_cc_array = json_decode($data['receivers_cc'], true);
                if ($receivers_cc_array && (int)$receivers_cc_array[0] ) {
                    foreach ($receivers_cc_array as $inbox_group) {
                        if ($inbox_group['inboxs']) {
                            foreach ($inbox_group['inboxs'] as $item) {
                                // Inbox to UserIds
                                $inbox = $InboxClass->getByID($item);
                                if ($inbox) {
                                    $inboxTemp = $inbox->getCollection(true);
                                    if ($inboxTemp['user_id']) {
                                        $userlist[] = $inboxTemp['user_id'];
                                    }
                                }
                                if (!in_array($item, $inboxlist)) {
                                    $inboxlist[] = $item;
                                }
                                if ( !$this->save([
                                    'inbox_id' => $item,
                                    'folder_id' => $folder_id_in,
                                    'body_id' => $body->lastID,
                                    'isConfirm' => $data['confirm']
                                ])) {
                                    return false;
                                }

                            }
                        }

                    }
                }
            }

/*
            echo '<pre>Userlist:';
            print_r($userlist);
            echo '</pre>';

            echo '<pre>';
            print_r($data['umfragen']);
            echo '</pre>';
*/
            if ($data['umfragen']  && EXTENSION::isActive('ext.zwiebelgasse.umfragen')) {
                include_once PATH_EXTENSIONS . 'umfragen' . DS . 'models' . DS . 'List.class.php';
                $UmfragenClass = new extUmfragenModelList();
                $data['umfragen'] = json_decode($data['umfragen']);

                $umfrageID = $UmfragenClass->setListWithItems([
                    'id' => 0,
                    'title' => $data['subject'],
                    'state' => 1,
                    'createdTime' => date('Y-m-d H:i', time()),
                    'createdUserID' => DB::getSession()->getUserID(),
                    'userlist' => 0,
                    'type' => 'ext_inbox'
                ], $data['umfragen']);

                if ($umfrageID) {
                    $bodyClass->update([
                        'id' => $body->lastID,
                        'umfrage' => $umfrageID
                    ]);
                }
            }


            // GESENDET
            if ( !$this->save([
                'inbox_id' => $sender_id,
                'folder_id' => $folder_id_out,
                'body_id' => $body->lastID,
                'isConfirm' => $data['confirm'],
                'isRead' => time()
            ])) {
                return false;
            }



            if ($data['messageParentID'] && ($data['isAnswer'] || $data['isForward'])) {

                if (!$this->update([
                    'id' => (int)$data['messageParentID'],
                    'isAnswer' => (int)$data['isAnswer'],
                    'isForward' => (int)$data['isForward']
                ])) {
                    return false;
                }
            }


            // Push
            if ($send && PUSH::active() && $InboxClass && $userlist) {
                foreach ($userlist as $useritem) {
                    PUSH::send($useritem, 'Neue Nachricht', $data['subject']);
                }
            }


            return true;

        }

        return false;

    }



    public function setUnread($userID = false)
    {
        $id = $this->getData('id');
        if (!(int)$id) {
            return false;
        }
        $time = time();

        if ( DB::run('UPDATE ' . $this->getModelTable() . ' SET isRead = :time
        WHERE id = :id  ', ['id' => $id, 'time' => 1 ]) ) {
            $this->setValue('isRead', $time);
            return true;
        }
        return false;
    }

    public function setRead($userID = false)
    {
        $id = $this->getData('id');
        if (!(int)$id) {
            return false;
        }
        $time = time();

        if ( DB::run('UPDATE ' . $this->getModelTable() . ' SET isRead = :time, isReadUser = :userID
        WHERE id = :id  ', ['id' => $id, 'time' => $time, 'userID' => $userID ]) ) {
            $this->setValue('isRead', $time);
            return true;
        }
        return false;
    }

    public function setConfirm()
    {
        $id = $this->getData('id');
        if (!(int)$id) {
            return false;
        }
        $time = time();

        if ( DB::run('UPDATE ' . $this->getModelTable() . ' SET isConfirm = :time
        WHERE id = :id  ', ['id' => $id, 'time' => $time ]) ) {
            $this->setValue('isConfirm', $time);
            return true;
        }
        return false;
    }

    public function setSend()
    {
        $id = $this->getData('id');
        if (!(int)$id) {
            return false;
        }
        $time = time();

        if ( DB::run('UPDATE ' . $this->getModelTable() . ' SET isEmail = :time
        WHERE id = :id  ', ['id' => $id, 'time' => $time ]) ) {
            $this->setValue('isEmail', $time);
            return true;
        }
        return false;
    }

    public function moveToFolderID($folder_id = false)
    {
        if (!(int)$folder_id) {
            return false;
        }
        $id = $this->getData('id');
        if (!(int)$id) {
            return false;
        }

        if ( DB::run('UPDATE ' . $this->getModelTable() . ' SET folder_id = :folder_id
        WHERE id = :id  ', ['folder_id' => $folder_id, 'id' => $id ]) ) {
            $this->setValue('folder_id', $folder_id);
            return true;
        }
        return false;
    }



}
