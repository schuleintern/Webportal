<?php


class extInboxFile extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-uplaod"></i> Inbox';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {


        $fid = (string)$_GET['fid'];

        if (!$fid) {
            new errorPage('Kein Zugriff');
        }

        include_once PATH_EXTENSION . 'models' . DS . 'MessageFile.class.php';

        $class = new extInboxModelMessageFile();
        $file = $class->getByUniqidID($fid);

        // TODO: check if file is in inbox from user // accsess
        if ($file) {
            $collection = $file->getCollection();
            FILE::getFile($collection['file'], $collection['name']);
        }


        exit;

    }


}
