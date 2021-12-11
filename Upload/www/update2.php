<?php

@error_reporting(E_ERROR);

/**
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
 * Version 1.4.0
 *
 */


include_once '../data/config/config.php';

class Updates {

    public static function to140($root) {

        // HIER DER UPDATE STUFF

        //$root->query('SELECT * FROM users');
        //$root->update('www/index.php');

        return true;
    }

}

class Update {

    private $rootUpdate = '../data/update/Upload/';
    private $folder = '../Backup_BeforeUpdateFrom';

    private $lastVersion = '';
    private $nextVersion = '';
    private $settings = false;
    private $mysqli = false;

    public function __construct($data) {

        if ($data['lastVersion']) {
            $this->lastVersion = $data['lastVersion'];
        }
        if ($data['nextVersion']) {
            $this->nextVersion = $data['nextVersion'];
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

    public function backupFile($folder) {
        return true;

        $path = explode('/', $folder);
        $file = array_pop($path);
        if (count($path) >= 1) {
            $deep = $this->folder;
            foreach($path as $dir) {
                $deep = $deep.'/'.$dir;
                mkdir($deep);
            }
        }
        if ( rename('../'.$folder, $this->folder.'/'.$folder) ) {
            return true;
        }
        return false;
    }

    public function copyFiles($folder) {
        return true;

        if ( file_exists($this->rootUpdate.$folder) ) {
            if ( rename($this->rootUpdate.$folder, '../'.$folder) ) {
                return true;
            }
        }
        return false;
    }

    public function update($folder) {
        if ($folder) {
            if ( $this->backupFile($folder) ) {
                if ( $this->copyFiles($folder) ) {
                    return true;
                }
            }
        }
        return false;
    }

    public function executeVersion() {
        $method = 'to'.str_replace('.','',$this->nextVersion);
        if ( method_exists( Updates, $method ) ) {
            if ( Updates::$method($this) ) {
                return true;
            }
        }
        return false;
    }


    public function query($query = false) {
        if (!$query) {
            return false;
        }
        $result = $this->mysqli->query($query);
        $return = array();
        while($row = mysqli_fetch_array($result)) {
            $return[] = $row;
        }
        return $return;
    }

}






$wartungsmodus = file_get_contents("../data/wartungsmodus/status.dat");
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

        $Update = new Update([
            "lastVersion" => $updateInfo['updateFromVersion'],
            "nextVersion" => '1.4.0' //$updateInfo['updateToVersion']
        ]);

    } else {
        echo("key fail.");
        exit(0);
    }

} else {
    header("Location: index.php?");
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SchuleIntern - Update</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="cssjs/dist/css/AdminLTE.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="cssjs/css/grid.css">
    <link rel="stylesheet" href="cssjs/css/style.css">
    <link rel="stylesheet" href="cssjs/css/si-components.css">
</head>
<body class="login-page">


<div class="login-box width-55vw">

    <div class="login-logo">
        <a href="index.php"><img src="index.php?page=SkinSettings&amp;action=getLogo" width="150" border="0"></a>
    </div>

    <div class="login-box-body si-form">

        <ul>
            <li><h1>SchuleIntern - Update</h1></li>
            <li>Update wird durchgeführt...</li>
            <?php if($Update->backupFile("framework")): ?>
                <li class="text-green">Alte Version gesichert.</li>
                <?php if($Update->copyFiles("framework")): ?>
                    <li class="text-green">Neue Version erfolgreich kopiert.</li>
                    <?php if($Update->executeVersion()): ?>
                        <li class="text-green">Versionsupdate erfolgreich durchgeführt</li>
                        <li class="text-green"><b>Einspielen der neuen Programmversion erfolgreich!</b></li>
                        <?php file_put_contents("../data/wartungsmodus/status.dat","") ?>
                        <li class="text-green">Wartungsmodus deaktiviert!</li>

                        <li>
                            <h2><a class="si-btn" href="index.php?page=Update&toVersion=<?= urlencode($updateInfo['updateToVersion']) ?>&key=<?= $updateInfo['updateKey'] ?>">
                                Update abschließen</a></h2>
                        </li>
                    <?php else: ?>
                        <li class="text-red">FEHLER: Neue Version konnte nicht eingespielt werden!</li>
                    <?php endif ?>
                <?php else: ?>
                    <li class="text-red">FEHLER: Neue Version konnte nicht kopiert werden!</li>
                <?php endif ?>
            <?php else: ?>
            <li class="text-red">FEHLER: Alter Version konnte nicht gesichert werden!</li>
            <?php endif ?>
        </ul>
    </div>

</div>

</body>
</html>