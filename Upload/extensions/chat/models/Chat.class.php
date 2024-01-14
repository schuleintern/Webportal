<?php
/**
 *
 */
class extChatModelChat
{

    /**
     * @var data []
     */
    private $data = [];



    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->setData($data);
    }

    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    /**
     * @return data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Getter
     */
    public function getID() {
        return $this->data['id'];
    }
    public function getUserID() {
        return $this->data['user_id'];
    }
    public function getGroupID() {
        return $this->data['group_id'];
    }
    public function getMember() {
        return new extChatModelMember( ["user_id" => $this->data['user_id'] ] );
    }
    public function getMsg() {
        return $this->data['msg'];
    }
    public function getMsgShort($length = 50) {

        if ($this->getFrom()['name']) {
            $msg = $this->getFrom()['name'].': '.$this->data['msg'];
        } else {
            $msg = $this->data['msg'];
        }
        if (strlen($msg) > $length ) {
            return substr($msg,0, $length).'...';
        }
        return $msg;

    }
    public function getTimeCreate() {
        return $this->data['timeCreate'];
    }


    public function getFrom() {
        $user = DB::getSession()->getUser();
        if ($user->getUserID() && $user->getUserID() == $this->getUserID() ) {
            return $user->getUserID();
        } else {
            return $this->getMember()->getID();
        }
    }

    public function getCollection() {
        $collection = [
            //"id" => $this->getID(),
            "from" => $this->getFrom(),
            "msg" => nl2br((string)$this->getMsg()),
            "timeCreate" => date("d.m.Y H:i", $this->getTimeCreate() )
        ];

        return $collection;
    }

    public static function setMsg($data) {
        if (!$data['group_id'] || !$data['msg']) {
            return false;
        }
        $user = DB::getSession()->getUser();
        if (!$user->getUserID()) {
            return false;
        }

        $group = new extChatModelGroups(['id' => $data['group_id']]);
        if (!$group->isMembers($user->getUserID())) {
            return false;
        }

        $data = [
            "status" => 1,
            "group_id" => (int)DB::getDB()->escapeString($data['group_id']),
            "user_id" => $user->getUserID(),
            "msg" => DB::getDB()->escapeString($data['msg']),
            "timeCreate" => time()
        ];
        if ( DB::getDB()->query("INSERT INTO ext_chat_msg
                (
                    status,
                    group_id,
                    user_id,
                    msg,
                    timeCreate
                )
                values(
                    ".$data['status'].",
                    ".$data['group_id'].",
                    ".$data['user_id'].",
                    '".$data['msg']."',
                    '".$data['timeCreate']."'
                )") ) {
            $data['id'] = DB::getDB()->insert_id();
            $msgObj = new extChatModelChat($data);
            $group->setLastMsg($msgObj);
            $group->setUnread($msgObj);
            return $msgObj->getCollection();
        }
        return false;

    }


    public static function getByID($id) {

        if (!(int)$id) {
            return false;
        }
        $user = DB::getSession()->getUser();
        $where = ' WHERE id = '.(int)$id;
        $data = DB::getDB()->query_first("SELECT * FROM ext_chat_msg ".$where);
        if ($data) {
            return new self($data);
        }
        return false;

    }
}