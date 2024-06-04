<?php
/**
 *
 */
class extUserlistModelMember
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
    public function __construct($data = false, $user = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        if ($user) {
            $this->user = $user;
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
    public function getListID() {
        return $this->data['list_id'];
    }
    public function getUserID() {
        return $this->data['user_id'];
    }


    public function getCollection($full = false) {


        if ($this->user) {
            $collection = $this->user->getCollection(false, true);

        } else {
            $collection = [
                "id" => $this->getUserID(),
                "list_id" => $this->getListID(),
                "member_id" => $this->getID()
            ];
            /*
            $user = $this->user->getCollection();
            $collection['vorname'] = $user['vorname'];
            $collection['nachname'] = $user['nachname'];
            $collection['name'] = $user['name'];
            $collection['type'] = $user['type'];
            */
        }

        return $collection;
    }






}