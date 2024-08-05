<?php

class extInboxCronMakeInboxs extends AbstractCron
{

    public function __construct()
    {

    }

    public function execute()
    {


        include_once PATH_EXTENSIONS.'inbox'.DS . 'models' . DS . 'Users.class.php';
        $Users = new extInboxModelUsers();
        $Users->makeUsers();

    }


    public function getName()
    {
        return "Make User Inboxs";
    }

    public function getDescription()
    {
        return "generate inbox_user from user table";
    }


    public function getCronResult()
    {
        return ['success' => 1, 'resultText' => 'Erfolgreich'];
    }

    public function informAdminIfFail()
    {
        return false;
    }

    public function executeEveryXSeconds()
    {
        return 10800;        // 3 stunden
    }


}