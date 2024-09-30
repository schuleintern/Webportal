<?php

class extGanztagsCronFetchPupils extends AbstractCron
{

    public function __construct()
    {

    }

    public function execute()
    {

        include_once PATH_EXTENSIONS.'ganztags'.DS . 'models' . DS . 'Schueler2.class.php';
        $class = new extGanztagsModelSchueler2();
        if ( !$class->setAllUnsigned() ) {
            return true;
        }
        return false;

    }


    public function getName()
    {
        return "Schüler in Ganztags laden";
    }

    public function getDescription()
    {
        return "ext_ganztags - Schueler mit Ganztags marker werden importiert";
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
        return 86400;        // 1 mal am tag
    }


}