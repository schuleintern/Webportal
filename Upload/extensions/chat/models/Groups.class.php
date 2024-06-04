<?php
/**
 *
 */


class extChatModelGroups
{

    /**
     * @var data []
     */
    private $data = [];


    private $members = false;
    private $chat = false;


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
    public function getTitle() {
        return $this->data['title'];
    }
    public function getStatus() {
        return $this->data['status'];
    }
    public function getLastMsgTime() {
        return $this->data['lastMsgTime'];
    }
    public function getLastMsgTimeHuman() {
        return date('d.m.Y', $this->data['lastMsgTime']);
    }
    public function getLastMsg() {
        return $this->data['lastMsg'];
    }
    public function getUnread() {
        return $this->data['unread'];
    }

    public function getLastMsgShort($length = 50) {

        if ($this->data['lastMsg']) {
            $message = extChatModelChat::getByID($this->data['lastMsg']);
            if ($message) {
                return $message->getMsgShort($length);
            }
        }
        return '';
    }

    public function setLastMsg($msgObj) {
        if (!$msgObj) {
            return false;
        }

        if ( DB::getDB()->query("UPDATE ext_chat_groups SET 
                           lastMsgTime=".$msgObj->getTimeCreate().", 
                           lastMsg=".$msgObj->getID()."
                           WHERE id = ".$this->getID() ) )  {
            return true;
        }
        return false;

    }

    public function getMembers() {
        if (!$this->members && $this->getID() ) {
            $this->members = $this->loadMembers( $this->getID() );
        }
        return $this->members;
    }
    public function setMembers($data) {

        if (!$data || !is_array($data)) {
            return false;
        }

        $members = $this->getMembers();

        foreach($members as $key => $member) {

            if ( in_array($member->getID(), $data) ) {
                //nothing
                //echo 'nothing '.$member->getID();
                unset($data[ array_search($member->getID(), $data) ]);
            } else {
                //remove
                //echo 'remove '.$member->getID();
                $member->removeGroup($this->getID());
            }
        }
        if (count($data) > 0) {
            foreach ($data as $item) {
                // add
                //echo 'add '.$item;
                $newMember = new extChatModelMember(['user_id' => $item]);
                $newMember->addGroup($this->getID());
            }
        }

        return true;
    }
    public function isMembers($user_id) {
        if (!$user_id) {
            return false;
        }
        $members = $this->getMembers();
        foreach($members as $member) {
            if ($member->getID() == $user_id) {
                return $member;
            }
        }
        return false;
    }

    public function getMembersCollection() {
        $items = $this->getMembers();
        $collection = [];
        foreach ($items as $item) {
            $collection[] = $item->getCollection();
        }
        return $collection;
    }
    private function loadMembers($id) {
        if ( !(int)$id ) {
            return false;
        }
        $items =  [];
        $dataSQL = DB::getDB()->query("SELECT user_id as user_id FROM ext_chat_groups_member WHERE group_id = ".(int)$id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $items[] = new extChatModelMember($data);
        }
        return $items;
    }


    public function getChat() {
        if (!$this->chat && $this->getID() ) {
            $this->chat = $this->loadChat( $this->getID() );
        }
        return $this->chat;
    }
    public function getChatCollection() {
        $items = $this->getChat();
        $collection = [];
        foreach ($items as $item) {
            $collection[] = $item->getCollection();
        }
        return $collection;
    }
    private function loadChat($id) {
        if ( !(int)$id ) {
            return false;
        }
        $items =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_chat_msg WHERE group_id = ".(int)$id." ORDER BY timeCreate" );
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $items[] = new extChatModelChat($data);
        }
        return $items;
    }

    public function setUnread($msg) {

        $members = $this->getMembers();
        foreach($members as $key => $member) {
            $member->setUnread($msg);
        }
        return true;
    }

    public function unsetUnread($int = 0) {
        $user = DB::getSession()->getUser();
        $members = $this->getMembers();
        foreach($members as $key => $member) {
            if ($member->getID() == $user->getUserID()) {
                $member->unsetUnread($this->getID($int));
            }
        }
        return true;
    }

    /**
     * @return Array[]
     */
    public static function getMyByStatus($status = 1) {

        // 1 = open
        // 0 = closed

        $user = DB::getSession()->getUser();

        $orderBy = 'b.lastMsgTime, b.id DESC';
        $where = ' WHERE a.user_id = "'.$user->getUserID().'"';
        if ($status) {
            $where .= 'b.status = "'.$status.'"';
        }
        $ret =  [];

        $dataSQL = DB::getDB()->query("SELECT a.user_id, a.unread, b.id, b.title, b.lastMsgTime, b.status, b.lastMsg
            FROM ext_chat_groups_member as a
			LEFT JOIN ext_chat_groups AS b ON a.group_id LIKE b.id 
			".$where."  ORDER BY ".$orderBy. " "); // GROUP BY b.id


        //$dataSQL = DB::getDB()->query("SELECT * FROM ext_chat_groups ".$where);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    public static  function getMyByID($id) {

        if (!$id) {
            return false;
        }

        $user = DB::getSession()->getUser();

        $where = ' WHERE a.group_id = '.$id.' AND a.user_id = "'.$user->getUserID().'"';

        $dataSQL = DB::getDB()->query("SELECT a.user_id , b.id, b.title, b.lastMsgtime, b.status
            FROM ext_chat_groups_member as a
			LEFT JOIN ext_chat_groups AS b ON a.group_id LIKE b.id 
			".$where);


        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            return new self($data);
        }

    }


    public static function setGroup($data) {

        if (!$data['title']) {
            return false;
        }
        $user = DB::getSession()->getUser();
        if (!$user->getUserID()) {
            return false;
        }
        $data = [
            "title" => DB::getDB()->escapeString($data['title']),
            "status" => (int)DB::getDB()->escapeString($data['status']),
            "id" => (int)DB::getDB()->escapeString($data['id'])
        ];

        if ($data['id']) {
            if ( DB::getDB()->query("UPDATE ext_chat_groups SET 
                           title='".$data['title']."', 
                           status='".$data['status']."'
                           WHERE id = ".$data['id']) ) {
                //$obj = new extChatModelGroups($data);
                //return $obj->getCollection();
                return $data;
            }
        } else {
            if ( DB::getDB()->query("INSERT INTO ext_chat_groups
                (
                    id,
                    title,
                    status
                )
                values(
                    ".$data['id'].",
                    '".$data['title']."',
                    ".$data['status']."
                )") ) {
                $data['id'] = DB::getDB()->insert_id();
                //$obj = new extChatModelGroups($data);
                //return $obj->getCollection();
                return $data;
            }
        }

        return false;

    }

}