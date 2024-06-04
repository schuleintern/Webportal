<?php

 

class extFileshareFile extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-uplaod"></i> Fileshare';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {


        $folder = (string)$_GET['folder'];
        $fid = (int)$_GET['fid'];

        if (!$folder || !$fid) {
            new errorPage('Kein Zugriff');
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Item.class.php';

        $FileShare = new extFileshareModelItem();
        $fileshare = $FileShare->getByFolderAndID($folder, $fid);

        if ($fileshare) {
            $collection = $fileshare->getCollection();

            FILE::getFile( PATH_DATA . 'ext_fileshare'.DS. $collection['folder'].DS.$collection['filename'], $collection['title'] );
        }


        exit;

    }


}
