<?php

class AdminUpdate extends AbstractPage {

	private $info;
	
	private $adminGroup = 'Webportal_Administrator';
	
	
	public function __construct() {
		die();	
	}

	public function execute() {
	    new errorPage();
	}
	
	private function index() {
	}
	
	public static function hasSettings() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return [];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Update';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Update_Admin';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-download';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-cogs';
	}
	
	public static function getAdminMenuGroup() {
		return 'System';
	}

	public static function siteIsAlwaysActive() {
		return true;
	}
	/**
	 * Überprüft, ob die Seite eine Administration hat.
	 * @return boolean
	 */
	public static function hasAdmin() {
		return true;
	}

	public static function displayAdministration($selfURL) {
	    $html = "";

        $currentVersion = DB::getVersion();

        $updateserver = DB::getGlobalSettings()->updateServer;

        $releaseID = DB::getSettings()->getValue("current-release-id");

        $infoToReleaseID = file_get_contents(DB::getGlobalSettings()->updateServer . "/api/release/" . $releaseID);

        $versionInfo = json_decode($infoToReleaseID, true);


        if($versionInfo['id'] > 0) {

            $versionInfoText = "Name: " . $versionInfo['name'] . "\r\n";
            $versionInfoText .= "Datum: " . $versionInfo['releaseDate'] . "\r\n";

            if($versionInfo['nextVersionID'] > 0) {
                // Update verfügbar
                $newVersion = file_get_contents(DB::getGlobalSettings()->updateServer . "/api/release/" . $versionInfo['nextVersionID']);
                $versionInfoNewVersion = json_decode($newVersion, true);

                if($_REQUEST['doUpdate'] > 0) {
                    return self::updateToVersion($versionInfoNewVersion);
                    exit(0);
                }

                $newVersionNumber = $versionInfoNewVersion['versionMajor'] . "." .$versionInfoNewVersion['versionMinor'] . "." .$versionInfoNewVersion['versionPatch'];


                $newVersionText = "Name: " . $versionInfoNewVersion['name'] . "\r\n";
                $newVersionText .= "Datum: " . $versionInfoNewVersion['releaseDate'] . "\r\n";
                $newVersionText .= "Veränderungen: " . $versionInfoNewVersion['changeLog'] . "\r\n";

                eval("\$html = \"" . DB::getTPL()->get("administration/update/updatestatusnew") . "\";");
            }
            else {
                eval("\$html = \"" . DB::getTPL()->get("administration/update/updatestatusok") . "\";");

            }

        }
        else {
            eval("\$html = \"" . DB::getTPL()->get("administration/update/updateservernotavailible") . "\";");
        }

		return $html;
	}

	private static function updateToVersion($versionInfo) {

        $url = DB::getGlobalSettings()->updateServer . "/api/release/" . $versionInfo['id'] . "/download";

        mkdir("../data/update");

        self::deleteAll("../data/update");

        file_put_contents("../data/update/update.zip", fopen($url, 'r'));

        $zip = new ZipArchive;
        if ($zip->open('../data/update/update.zip') === TRUE) {
            $zip->extractTo('../data/update/');
            $zip->close();
        } else {
            die('Installationsdatei konnte nicht entpackt werden.');
        }

        $random = random_int(10000000,99999999999);

        // Wartungsinformation eintragen

        $wartungsinfo = [
            'pendingUpdate' => true,
            'updateKey' => $random,
            'updateFromReleaseID' => DB::getSettings()->getInteger("current-release-id"),
            'updateFromVersion' => DB::getVersion(),
            'updateToVersion' => $versionInfo['versionMajor'] . "." .$versionInfo['versionMinor'] . "." .$versionInfo['versionPatch'],
            'updateToReleaseID' => $versionInfo['id']
        ];

        file_put_contents("../data/update.json", json_encode($wartungsinfo));

        $html = "";

        eval("\$html = \"" . DB::getTPL()->get("administration/update/updatedownloaded") . "\";");

	    // Wartungsmodus, um Frameworkdateien freizugeben.
        file_put_contents("../data/wartungsmodus/status.dat","heute");

        return $html;
    }

    private static function deleteAll($dir) {
	    if(is_file($dir)) unlink($dir);
	    else if(is_dir($dir)) {
            $dirContent = opendir($dir);

            while($content = readdir($dirContent)) {
                if($content != '.' && $content != "..") {
                    self::deleteAll($content);
                }
            }

            return @rmdir($dir);
        }
    }
		
}


?>