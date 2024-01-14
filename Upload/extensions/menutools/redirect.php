<?php

 

class extMenutoolsRedirect extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-sun"></i> Menu Tools';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {
        $_request = $this->getRequest();

        if ($_request['href']) {
            header('Location: ' .$_request['href']);
            exit;
        }
        header('Location: index.php');
        exit;
    }
}
