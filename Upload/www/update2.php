<?php

@error_reporting(E_ERROR);

/**
 *
 * Software is licensed unter GPLv2
 *
 * (c) Christian Spitschka, 2012-2019
 *
 * _______  _______                    _        _______ _________ _       _________ _______  _______  _
 * (  ____ \(  ____ \|\     /||\     /|( \      (  ____ \\__   __/( (    /|\__   __/(  ____ \(  ____ )( (    /|
 * | (    \/| (    \/| )   ( || )   ( || (      | (    \/   ) (   |  \  ( |   ) (   | (    \/| (    )||  \  ( |
 * | (_____ | |      | (___) || |   | || |      | (__       | |   |   \ | |   | |   | (__    | (____)||   \ | |
 * (_____  )| |      |  ___  || |   | || |      |  __)      | |   | (\ \) |   | |   |  __)   |     __)| (\ \) |
 *       ) || |      | (   ) || |   | || |      | (         | |   | | \   |   | |   | (      | (\ (   | | \   |
 * /\____) || (____/\| )   ( || (___) || (____/\| (____/\___) (___| )  \  |   | |   | (____/\| ) \ \__| )  \  |
 * \_______)(_______/|/     \|(_______)(_______/(_______/\_______/|/    )_)   )_(   (_______/|/   \__/|/    )_)
 *
 *
 * Version 1.2.0
 *
 */

include_once '../data/config/config.php';

class Updates {
    public static function to140() {
        echo 'wuhu!!!!';
    }
}

class Update {

    private $rootUpdate = '../data/update/Upload/';
    private $folder = '../Backup_BeforeUpdateFrom';

    private $lastVersion = '';
    private $settings = false;
    private $mysqli = false;

    public function __construct($data) {

        if ($data['lastVersion']) {
            $this->lastVersion = $data['lastVersion'];
        }
        $this->folder .= '-'.$this->lastVersion.'-'.date('Y-m-d',time());

        $this->settings = new GlobalSettings();

        $this->mysqli = new mysqli($this->settings->dbSettigns['host'], $this->settings->dbSettigns['user'], $this->settings->dbSettigns['password'], $this->settings->dbSettigns['database']);
        if ($this->mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
            exit;
        }

        if (!is_dir($this->folder)) {
            mkdir($this->folder);
        }

    }


    public function backupFolder($folder) {
        if ( rename('../'.$folder, $this->folder.'/'.$folder) ) {
            return true;
        }
        return false;
    }

    public function copyFolder($folder) {
        if ( rename($this->rootUpdate.$folder, '../'.$folder) ) {
            return true;
        }
        return false;
    }

    public function updateDatabase() {

        // TODO: woher kommen die Querys???
        // FILE.sql oder function() ???
        $method = 'to'.$this->lastVersion;

        if (method_exists( Updates::$method )) {
            echo  'go!!!!!!';
            Updates::$method();
        }
    }


    public function querys() {

        $result = $mysqli->query("SELECT * FROM users");

        $tables = array();
        while($row = mysqli_fetch_array($result)) {
            /* We return the size in Kilobytes */
            $total_size = ($row[ "Data_length" ] +
                    $row[ "Index_length" ]) / 1024;
            $tables[$row['Name']] = sprintf("%.2f", $total_size);
        }

    }

}



$wartungsmodus = file_get_contents("../data/wartungsmodus/status.dat");

/*
if($wartungsmodus != "") {
    // Im Wartungsmodus.
    // Update könnte ausgeführt werden.

    $updateInfo = file_get_contents("../data/update.json");

    if($updateInfo === false) {
        echo("update fail. (not availible)");
        exit(0);
    }

    $updateInfo = json_decode($updateInfo, true);

    if($_REQUEST['key'] == $updateInfo['updateKey']) {
*/

        $Update = new Update([
            "lastVersion" => $updateInfo['updateFromVersion']
        ]);
        ?>

        <html>
        <head>
            <title>Schule-Intern Update</title>
        </head>
        <body>

        Update wird durchgeführt...<br /><br />

        Sichere alte Anwendungsdaten...<br />
        <?php

        if($Update->backupFolder("framework") {

            echo("Alte Version gesichert.<br >");
            echo("Spiele neue Programmversion ein...<br />");


            if($Update->copyFolder("framework")) {

                echo("Einspielen der Datenbankänderungen");
                $Update->updateDatabase();


                echo("Einspielen der neuen Programmversion erfolgreich.");
                echo("<br />");
                echo("Wartungsmodus wird deaktiviert.<br >");
                file_put_contents("../data/wartungsmodus/status.dat","");
                echo("Wartungsmodus deaktiviert.<br >");
                echo("Installtion abschließen:<br />");

                echo("<a href=\"index.php?page=Update&toVersion=" . urlencode($updateInfo['updateToVersion']) . "&key=" . $updateInfo['updateKey'] . "\">Update abschließen</a>");

            }
            else {
                echo("<font color=\"red\">FEHLER: NEUE VERSION KONNTE NICHT KOPIERT WERDEN!</font><br />");
            }

        }
        else {
            echo("<font color=\"red\">FEHLER: ALTE VERSION KONNTE NICHT GELÖSCHT WERDEN!</font><br />");
        }

        ?>

        </body>


        </html>


        <?php
/*
    }
    else {
        echo("key fail.");
        exit(0);
    }

}

else {
    header("Location: index.php?");
}
*/



