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
 * Version 1.5.0
 *
 */


include_once '../data/config/config.php';

class Updates
{

    public static function to150($root)
    {

        $root->query("ALTER TABLE `user_settings` ADD COLUMN `autoLogout` int(11) DEFAULT NULL;", false);
        $root->query("ALTER TABLE `menu_item` ADD COLUMN `options` TEXT;", false);
        $root->query("ALTER TABLE `menu_item` ADD COLUMN `target` tinyint(1) DEFAULT NULL;", false);
        $root->query("ALTER TABLE `messages_messages` ADD COLUMN `messageGroupID` int(1) DEFAULT NULL;", false);
        $root->query("ALTER TABLE `messages_messages` MODIFY COLUMN `messageFolder` enum('POSTEINGANG','GESENDETE','PAPIERKORB','ANDERER','ARCHIV','ENTWURF') NOT NULL;", false);


        return true;
    }

    public static function to142($root)
    {

        return true;
    }

    public static function to141($root)
    {

        return true;
    }

    public static function to140($root)
    {

        // HIER DER UPDATE STUFF

        //$root->query('SELECT * FROM users');
        //$root->update('www/index.php');

        // Add Extensions to Config
        $path = '../data/config/config.php';
        $config = file_get_contents($path);
        $config = substr($config, 0, strrpos($config, '}'));
        $config = $config . '
    /**
     * Domain des Extension Servers
     * @var string
     */
    public $extensionsServer = "https://extensions.schule-intern.de//";
}';
        file_put_contents($path, $config);


        $root->update('www/startup.php');
        $root->update('www/update.php');

        $root->update('www/components');
        $root->update('www/images');
        $root->update('www/cssjs');


        $root->query("CREATE TABLE `extensions` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) DEFAULT NULL,
              `uniqid` varchar(255) DEFAULT NULL,
              `version` int(11) DEFAULT NULL,
              `active` tinyint(11) DEFAULT NULL,
              `folder` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`),
            KEY `uniqid` (`uniqid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $root->query("CREATE TABLE `raumplan_stunden` (
                `stundeID` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `stundenplanID` int(11) DEFAULT NULL,
                `stundeKlasse` varchar(20) DEFAULT NULL,
                `stundeLehrer` varchar(20) DEFAULT NULL,
                `stundeFach` varchar(20) DEFAULT NULL,
                `stundeRaum` varchar(20) DEFAULT NULL,
                `stundeDatum` date DEFAULT NULL,
                `stundeStunde` int(2) DEFAULT NULL,
                PRIMARY KEY (`stundeID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $root->query("CREATE TABLE `kalender_allInOne` (
            `kalenderID`        int(11) NOT NULL AUTO_INCREMENT,
            `kalenderName`      varchar(255) NOT NULL,
            `kalenderColor`     varchar(7) DEFAULT NULL,
            `kalenderSort`      tinyint(1) DEFAULT NULL,
            `kalenderPreSelect` tinyint(1) DEFAULT NULL,
            `kalenderAcl`       int(11) DEFAULT NULL,
            `kalenderFerien`    tinyint(1) DEFAULT '0',
            `kalenderPublic`    tinyint(1) DEFAULT NULL,
            PRIMARY KEY (`kalenderID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");

        $root->query("INSERT INTO `kalender_allInOne` (`kalenderID`, `kalenderName`, `kalenderColor`, `kalenderSort`, `kalenderPreSelect`,
                                 `kalenderAcl`, `kalenderFerien`, `kalenderPublic`)
        VALUES (1, 'Schulkalender', '#2E64FE', 1, 1, 4, 0, 1),
               (2, 'Interner Kalender', '#FE2E64', 2, 1, 5, 0, 0),
               (3, 'Ferien', '#00a65a', 3, 1, 6, 1, 1);");

        $root->query("CREATE TABLE `kalender_allInOne_eintrag`
        (
            `eintragID`           int(11) NOT NULL AUTO_INCREMENT,
            `kalenderID`          int(11) NOT NULL,
            `eintragKategorieID`  int(11) NOT NULL DEFAULT '0',
            `eintragTitel`        varchar(255) NOT NULL,
            `eintragDatumStart`   date         NOT NULL,
            `eintragTimeStart`    time         NOT NULL,
            `eintragDatumEnde`    date         NOT NULL,
            `eintragTimeEnde`     time         NOT NULL,
            `eintragOrt`          varchar(255) NOT NULL,
            `eintragKommentar`    tinytext     NOT NULL,
            `eintragUserID`       int(11) NOT NULL,
            `eintragCreatedTime`  datetime     NOT NULL,
            `eintragModifiedTime` datetime     NOT NULL,
            PRIMARY KEY (`eintragID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");

        $root->query("CREATE TABLE `kalender_allInOne_kategorie` (
           `kategorieID` int(11) NOT NULL AUTO_INCREMENT,
           `kategorieKalenderID` int(11) NOT NULL,
           `kategorieName` varchar(255) NOT NULL,
           `kategorieFarbe` varchar(7) NOT NULL,
           `kategorieIcon` varchar(255) NOT NULL,
               PRIMARY KEY (`kategorieID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");


        $root->query("CREATE TABLE `menu` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `alias` varchar(100) DEFAULT NULL,
            `title` varchar(100) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $root->query("INSERT INTO `menu` (`id`, `alias`, `title`)
            VALUES (1,'main','Hauptmenü');  ");

        $root->query("CREATE TABLE `menu_item` (
             `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
             `active` tinyint(1) DEFAULT '0',
             `menu_id` int(11) NOT NULL,
             `parent_id` int(11) NOT NULL,
             `sort` int(3) DEFAULT '0',
             `page` varchar(100) DEFAULT '',
             `title` varchar(100) NOT NULL DEFAULT '',
             `icon` varchar(100) DEFAULT NULL,
             `params` text,
             `access` varchar(255) DEFAULT NULL,
             PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $root->query("INSERT INTO `menu_item` (`id`, `active`, `menu_id`, `parent_id`, `sort`, `page`, `title`, `icon`, `params`, `access`)
        VALUES
            (1,1,1,0,0,'','Aktuelles','fa fa-clock',NULL,NULL),
            (2,1,1,0,0,'','Informationen','fa fa-clock',NULL,NULL),
            (3,1,1,0,0,'','Lehreranwendungen','fa fa-graduation-cap',NULL,NULL),
            (4,1,1,0,0,'','Verwaltung','fa fas fa-pencil-alt-square',NULL,NULL),
            (5,1,1,0,0,'','Benutzeraccount / Nachrichten','fa fa-user',NULL,NULL),
            (6,1,1,0,0,'','Unterricht','fa fa-graduation-cap',NULL,NULL),
            (7,1,1,0,0,'','Administration','fa fa-cogs',NULL,NULL);");


        $root->query("CREATE TABLE `widgets` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `uniqid` varchar(100) DEFAULT NULL,
            `position` varchar(100) DEFAULT NULL,
            `access` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `uniqid` (`uniqid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


        return true;
    }

    public static function to134($root)
    {

        return true;
    }

    public static function to133($root)
    {

        return true;
    }

}

class Update
{

    private $rootUpdate = '../data/update/Upload/';
    private $folder = '../Backup_BeforeUpdateFrom';

    private $lastVersion = '';
    private $nextVersion = '';
    private $settings = false;
    private $mysqli = false;

    public function __construct($data)
    {

        if ($data['lastVersion']) {
            $this->lastVersion = $data['lastVersion'];
        }
        if ($data['nextVersion']) {
            $this->nextVersion = $data['nextVersion'];
        }
        $this->folder .= '-' . $this->lastVersion . '-' . date('Y-m-d', time());

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

    public function backupFile($folder)
    {

        $path = explode('/', $folder);
        $file = array_pop($path);
        if (count($path) >= 1) {
            $deep = $this->folder;
            foreach ($path as $dir) {
                $deep = $deep . '/' . $dir;
                mkdir($deep);
            }
        }
        if (rename('../' . $folder, $this->folder . '/' . $folder)) {
            return true;
        }
        return false;
    }

    public function copyFiles($folder)
    {

        if (file_exists($this->rootUpdate . $folder)) {
            if (rename($this->rootUpdate . $folder, '../' . $folder)) {
                return true;
            }
        }
        return false;
    }

    public function update($folder)
    {
        if ($folder) {
            if ($this->backupFile($folder)) {
                if ($this->copyFiles($folder)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function executeVersion()
    {
        $method = 'to' . str_replace('.', '', $this->nextVersion);
        if (method_exists(Updates, $method)) {
            if (Updates::$method($this) != true) {
                return false;
            }
        }
        return true;
    }


    public function query($query = false, $fetch = true)
    {
        if (!$query) {
            return false;
        }
        $result = $this->mysqli->query($query);
        if ($result && $fetch) {
            $return = array();
            while ($row = $result->fetch_array()) {
                $return[] = $row;
            }
            return $return;
        } else if ($result && $fetch == false) {
            return true;
        }
        return false;

    }

}


$wartungsmodus = file_get_contents("../data/wartungsmodus/status.dat");
if ($wartungsmodus != "") {
    // Im Wartungsmodus.
    // Update könnte ausgeführt werden.

    $updateInfo = file_get_contents("../data/update.json");
    if ($updateInfo === false) {
        echo("update fail. (not availible)");
        exit(0);
    }
    $updateInfo = json_decode($updateInfo, true);

    if ($_REQUEST['key'] == $updateInfo['updateKey']) {

        $Update = new Update([
            "lastVersion" => $updateInfo['updateFromVersion'],
            "nextVersion" => $updateInfo['updateToVersion']
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
        <a href="index.php"><img src="images/Icon.png" width="150" border="0"></a>
    </div>

    <div class="login-box-body si-form">

        <ul>
            <li><h1>SchuleIntern - Update</h1></li>
            <li>Update wird durchgeführt...</li>
            <?php if ($Update->backupFile("framework")): ?>
                <li class="text-green">Alte Version gesichert.</li>
                <?php if ($Update->copyFiles("framework")): ?>
                    <li class="text-green">Neue Version erfolgreich kopiert.</li>
                    <?php if ($Update->executeVersion()): ?>
                        <li class="text-green">Versionsupdate erfolgreich durchgeführt</li>
                        <li class="text-green"><b>Einspielen der neuen Programmversion erfolgreich!</b></li>
                        <?php file_put_contents("../data/wartungsmodus/status.dat", "") ?>
                        <li class="text-green">Wartungsmodus deaktiviert!</li>

                        <li>
                            <h2><a class="si-btn"
                                   href="index.php?page=Update&toVersion=<?= urlencode($updateInfo['updateToVersion']) ?>&key=<?= $updateInfo['updateKey'] ?>">
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