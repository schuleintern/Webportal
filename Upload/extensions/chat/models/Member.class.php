<?php
/**
 *
 */
class extChatModelMember
{

    /**
     * @var data []
     */
    private $data = [];

    private $user = false;

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
        return $this->data['user_id'];
    }
    public function getUser() {
        if (!$this->user && $this->getID() ) {
            $this->user = user::getUserByID($this->getID());
        }
        return $this->user;
    }

    public function getCollection() {
        if ($this->getUser()) {
            $collection = [
                "id" => $this->getID(),
                "vorname" => $this->getUser()->getFirstName(),
                "nachname" => $this->getUser()->getLastName(),
                "name" => $this->getUser()->getDisplayName(),
                "type" => $this->getUser()->getUserTyp(true)
            ];
            return $collection;
        }

    }


    public function removeGroup($group_id) {

        if (!$group_id) {
            return false;
        }
        if (!$this->getID()) {
            return false;
        }
        if ( DB::getDB()->query("DELETE FROM ext_chat_groups_member WHERE user_id=".(int)$this->getID()." AND group_id=".(int)$group_id) ) {
            return true;
        }
        return false;
    }

    public function addGroup($group_id) {

        if (!$group_id) {
            return false;
        }
        if (!$this->getID()) {
            return false;
        }
        if ( DB::getDB()->query("INSERT INTO ext_chat_groups_member
                (
                    group_id,
                    user_id
                )
                values(
                    ".(int)$group_id.",
                    ".(int)$this->getID()."
                )") ) {
            return true;
        }
        return false;
    }

    public function setUnread($msg) {
        if (!$msg->getGroupID()) {
            return false;
        }
        if (!$this->getID()) {
            return false;
        }

        // Nicht beim Ersteller!
        if ($this->getID() == $msg->getUserID()) {
            return false;
        }

        if ( DB::getDB()->query("UPDATE ext_chat_groups_member SET unread = unread + 1
                           WHERE group_id = ".$msg->getGroupID()." AND user_id= ".$this->getID()) ) {
            return true;
        }
        return false;
    }

    public function unsetUnread($group_id, $int = 0) {

        if (!$group_id) {
            return false;
        }
        if (!$this->getID()) {
            return false;
        }

        if ( DB::getDB()->query("UPDATE ext_chat_groups_member SET unread = ".(int)$int."
                           WHERE group_id = ".$group_id." AND user_id= ".$this->getID()) ) {
            return true;
        }
        return false;
    }

}