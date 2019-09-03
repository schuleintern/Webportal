<?php

// Erzeugt ein Backup der SchuleIntern Instanz

include("vendor/autoload.php");

$backupStartTime = time();

$backupInfoFile = [];

$backupInfoFile['StartTime'] = $backupStartTime;

$backupName = date("Y-m-d;" . $backupStartTime);

$filename = "../data/backup/" . $backupName . ".zip";

$zip = new ZipArchive();

if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
    exit("cannot open <$filename>\n");
}

// Schritt1:
// Datenbankbackup erzeugen

include("../data/config/config.php");
$settings = new GlobalSettings();

echo('Erstelle Backup der Datenbank nach backup/' . $backupName . "_Database.sql\r\n");

try {
    $dump = new \Ifsnop\Mysqldump\Mysqldump('mysql:host=' . $settings->dbSettigns['host']. ';dbname=' . $settings->dbSettigns['database']. '', $settings->dbSettigns['user'], $settings->dbSettigns['password']);
    $dump->start('../data/backup/' . $backupName . "_Database.sql");
} catch (\Exception $e) {
    echo 'Fehler bei der Sicherung der Datenbank: ' . $e->getMessage();
    exit(1);
}
echo("Erstellen des Datenbankbackups fertig.\r\n");

echo("Datenbankbackup zur ZIP Datei hinzufügen...\r\n");
$zip->addFile('../data/backup/' . $backupName . "_Database.sql", "Database.sql");
echo("Datenbankbackup zur ZIP Datei hinzugefügt.\r\n");

$backupFolders = [
    'beobachtungsboegen',
    'config',
    'dokumente',
    'imageUploads',
    'uploads'
];

echo("-------------------------------------\r\n");
echo("Sichere Dateien...\r\n");

for($i = 0; $i < sizeof($backupFolders); $i++) {
    echo("Sichere Ordner \"" . $backupFolders[$i] . "\"...\r\n");

    $dir = opendir("../data/" . $backupFolders[$i]);

    while($file = readdir($dir)) {
        if($file == "." || $file == "..") continue;

        $zip->addFile("../data/" . $backupFolders[$i] . "/" . $file, "data/" . $backupFolders[$i] . "/" . $file);
    }
}


$mysqli = new mysqli($settings->dbSettigns['host'], $settings->dbSettigns['user'], $settings->dbSettigns['password'], $settings->dbSettigns['database']);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit(1);
}

$queryResult = $mysqli->query("SELECT * FROM settings WHERE settingName='currentVersion'");
$row = mysqli_fetch_assoc($queryResult);


$backupInfoFile['BackupEndTime'] = time();

$backupInfoFile['SoftwareVersion'] = $row['settingValue'];

$backupInfoFileText = json_encode($backupInfoFile);

$zip->addFromString("BackupInfo.json", $backupInfoFileText);

$zip->close();

@unlink('../data/backup/' . $backupName . "_Database.sql");



echo("Backup erfolfolgreich. Datei: \"../data/backup/" . $backupName . ".zip");

exit(0);


