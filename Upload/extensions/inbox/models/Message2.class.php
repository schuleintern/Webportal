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
        'isConfirm'
    ];

    
    static $defaults = [
        'isRead' => 0
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



    public function getCollection($full = false, $withText = false, $confirm = false, $files = false)
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
            $bodyCollection = $body->getCollection();

            $collection['subject'] = $bodyCollection['subject'];
            $collection['from'] = $body->getSenderCollection();
            $collection['to'] = $body->getReceiversShort();
            $collection['toCC'] = $body->getReceiversCC();
            $collection['priority'] = $bodyCollection['priority'];
            $collection['files'] = $bodyCollection['files'];
            $collection['date'] = date("d.m.Y H:i", strtotime($bodyCollection['createdTime']) );

            if ($withText) {
                $collection['text'] = nl2br($bodyCollection['text']);
            }

            if ($collection['isRead']) {
                $collection['isReadDate'] = date('d.m.Y H:i',$collection['isRead']);
            }
            if ($collection['isReadUser']) {
                $collection['isReadUser'] = User::getCollectionByID($collection['isReadUser']);
            }


        }

        if ($files) {

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
        }

        if ($confirm) {

            if ($this->getData('isConfirm') == 1) {


                include_once PATH_EXTENSIONS . 'inbox' . DS . 'models' . DS . 'Inbox2.class.php';
                $classInbox = new extInboxModelInbox2();

                $collection['confirmList'] = [
                    'to' => [],
                    'toCC' => [],
                    'toBCC' => []
                ];


                $arr = $body->getReceiversLong();
                foreach ($arr as $item) {
                    if ($item['inboxs']) {


                        $className = explode('::', $item['typ']);
                        $className = 'extInboxRecipient'.ucfirst($className[0]).ucfirst($className[1]);
                        $typ = str_replace('::','_', $item['typ']);
                        include_once PATH_EXTENSIONS.'inbox'.DS . 'inboxs' . DS . $typ.'.class.php';
                        $tmp_data = $className::getTitle($item['content']);


                        $foo = [
                            "title" => $tmp_data,
                            "inboxs" => []
                        ];

                        foreach($item['inboxs'] as $inbox) {
                            $msg_temp = $this->getMessageByInboxBody( $inbox, $this->getData('body_id') );
                            $inbox_tmp = $classInbox->getByID($inbox);
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
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE inbox_id = :inbox_id AND folder_id = :folder_id  ', ['inbox_id' => $inbox_id, 'folder_id' => $folder_id])->fetchAll();

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



    public function sendMessage($data = false)
    {
        if (!$data) {
            return false;
        }

        if (!$data['receiver'] || $data['receiver'] == '') {
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


        $receivers_array = json_decode($data['receiver'], true);

        if ( !$receivers_array || count($receivers_array) < 1 ) {
            return false;
        }


        $subject = DB::getDB()->escapeString(trim((string)$data['subject']));
        $text = DB::getDB()->escapeString(trim((string)$data['text']));

        $createTime = date('Y-m-d H:i', time());

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
            'files' => $data['files'],
            'isPrivat' => $data['isPrivat'],
            'noAnswer' => $data['noAnswer'],
            'files' => $data['files']
        ]);




        //$body_id = extInboxModelMessageBody::setDatabase($data['sender_id'], (string)$data['receiver'], $receiver_cc, $createTime, $subject, $text, $priority, $files);

        if ( $body && $body->lastID ) {


            // POSTEINGANG
            foreach ($receivers_array as $inbox_group) {
                if ($inbox_group['inboxs']) {
                    foreach ($inbox_group['inboxs'] as $item) {
                        if ( !$this->save([
                            'inbox_id' => $item,
                            'folder_id' => 1,
                            'body_id' => $body->lastID,
                            'isConfirm' => $data['confirm']
                        ])) {
                            return false;
                        }
                    }
                }
            }


            // POSTEINGANG CC
            if ($data['receivers_cc']) {
                $receivers_cc_array = json_decode($data['receivers_cc'], true);
                if ($receivers_cc_array && (int)$receivers_cc_array[0] ) {
                    foreach ($receivers_cc_array as $inbox_group) {
                        if ($inbox_group['inboxs']) {
                            foreach ($inbox_group['inboxs'] as $item) {
                                if ( !$this->save([
                                    'inbox_id' => $item,
                                    'folder_id' => 1,
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

            // GESENDET
            if ( !$this->save([
                'inbox_id' => $sender_id,
                'folder_id' => 2,
                'body_id' => $body->lastID,
                'isConfirm' => $data['confirm']
            ])) {
                return false;
            }

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
            //$this->data['isRead'] = $time;
            $this->setValue('isConfirm', $time);
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
