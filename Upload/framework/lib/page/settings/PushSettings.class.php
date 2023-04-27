<?php

use Minishlink\WebPush\VAPID;

class PushSettings extends AbstractPage
{

    public function __construct()
    {
    }

    public function execute()
    {

    }

    public static function getSettingsDescription()
    {
        return array(
            [
                'name' => 'global-push-publicKey',
                'typ' => 'ZEILE',
                'titel' => 'Public Key',
                'text' => ''
            ],
            [
                'name' => 'global-push-privateKey',
                'typ' => 'ZEILE',
                'titel' => 'Private Key',
                'text' => ''
            ]
        );
    }

    public static function getSiteDisplayName()
    {
        return "Push-Nachrichten";
    }

    public static function hasSettings()
    {
        return true;
    }

    public static function getUserGroups()
    {
        return array();

    }

    public static function siteIsAlwaysActive()
    {
        return true;
    }

    public static function hasAdmin()
    {
        return true;
    }

    public static function getAdminGroup()
    {
        return 'Webportal_Admin_Skin_Settings';
    }

    public static function getAdminMenuGroup()
    {
        return 'Allgemeine Einstellungen';
    }

    public static function displayAdministration($selfURL)
    {

        if ($_GET['task'] == 'setPushActive') {

            if ((int)$_POST['value'] == 1) {
                DB::getSettings()->setValue('global-push-active', true);

                require "../framework/lib/composer/vendor/autoload.php";
                $keys = VAPID::createVapidKeys();
                if ($keys) {
                    DB::getSettings()->setValue('global-push-publicKey', $keys['publicKey']);
                    DB::getSettings()->setValue('global-push-privateKey', $keys['privateKey']);
                }

            } else {
                DB::getSettings()->setValue('global-push-active', false);
                DB::getSettings()->setValue('global-push-publicKey', false);
                DB::getSettings()->setValue('global-push-privateKey', false);
            }

            echo json_encode( ['active' => DB::getSettings()->getBoolean('global-push-active') ]);
            exit;
        }


        $html = "";


        $pushActive = DB::getSettings()->getBoolean('global-push-active');

        $publicKey = DB::getSettings()->getValue('global-push-publicKey');
        $privateKey = DB::getSettings()->getValue('global-push-privateKey');

        if ($pushActive) {
            if ($publicKey == '' || $privateKey == '') {
                $pushActive = false;
                DB::getSettings()->setValue('global-push-active', false);
            }
        }

        eval("\$html = \"" . DB::getTPL()->get("settings/push") . "\";");

        return $html;

    }


}

?>
