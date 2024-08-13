<?php

class extKlassenkalenderCronFetchKlassen extends AbstractCron
{

    public function __construct()
    {

    }

    public function execute()
    {
        $klassen = klasseDB::getAll();

        include_once PATH_EXTENSIONS . 'klassenkalender' . DS . 'models' . DS . 'Kalender.class.php';
        $class = new extKlassenkalenderModelKalender();
        $calenders = $class->getAll();

        $i = 0;
        foreach ($klassen as $klasse) {
            $found = false;
            $foundCalender = false;

            foreach ($calenders as $calender) {
                if ($klasse->getKlassenname() == $calender->getData('title')) {
                    $found = $calender->getID();
                    $foundCalender = $calender;
                }
            }
            $admins = [];
            $teachers = $klasse->getTeachers();
            foreach ($teachers as $teacher) {
                $admins[] = $teacher->getUserID();
            }
            $admins = array_unique($admins);


            if (!$found) {

                $acl = ACL::getBlank();
                $acl['aclModuleClassParent'] = 'ext_klassenkalender';
                $acl['groups']['lehrer']['read'] = 1;
                $acl['groups']['none']['read'] = 1;
                $aclReturn = ACL::setAcl($acl);

                $i++;
                if ($aclReturn['aclID']) {
                    if (!$class->save([
                        'title' => $klasse->getKlassenname(),
                        'createdTime' => date('Y-m-d H:i:s', time()),
                        'createdUserID' => 0,
                        'color' => self::makeColor($klasse->getKlassenname()),
                        'acl' => $aclReturn['aclID'],
                        'state' => 1,
                        'sort' => $i,
                        'admins' => json_encode($admins)
                    ], true)) {

                    }
                }

            } else {

                $foundCalender->setValue('admins',json_encode($admins));
                // Update
                if (!$class->save($foundCalender)) {

                }
            }

        }

    }

    private static function makeColor($name)
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

        /*
        $iname = (int)$name;
        switch ($iname) {
            case '5':
                if (strtolower($name) == '5a') {
                    return '#46CAA9';
                } else if (strtolower($name) == '5b') {
                    return '##6BBDA8';
                } else if (strtolower($name) == '5c') {
                    return '#46CAA9';
                } else if (strtolower($name) == '5d') {
                    return '#46CAA9';
                } else if (strtolower($name) == '5e') {
                    return '#46CAA9';
                } else  {
                    return '#46CAA9';
                }
                break;
            default:
                return '#2C97F5';
                break;
        }
        */
    }

    public function getName()
    {
        return "Sync Klassen mit Klassenkalender";
    }

    public function getDescription()
    {
        return "sync die Klassen und die Klassenlehrer mit den Klassenkalender";
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