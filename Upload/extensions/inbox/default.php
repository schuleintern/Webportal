<?php

 

class extInboxDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-envelope"></i> Nachrichten';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }


    public function execute()
    {

        $request = $this->getRequest();
        $acl = $this->getAcl();

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        /*
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }
        */

        $ret = [];
        include_once PATH_EXTENSION . 'models' . DS . 'Inbox2.class.php';
        $class = new extInboxModelInbox2();
        $tmp_data = $class->getByUserID($userID);

        if ($tmp_data) {
            foreach ($tmp_data as $item) {
                $ret[] = $item->getCollection(true, true, false, true);
            }
        }

        $inbox_id = $request['iid'];
        $message_id = $request['mid'];

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js'
            ],
            "data" => [
                "acl" => $acl['rights'],
                "apiURL" => "rest.php/inbox",
                "data" => $ret,
                "inbox_id" => $inbox_id,
                "message_id" => $message_id,
                "apiKey" => DB::getGlobalSettings()->apiKey

            ]
        ]);

    }


}
