<?php
error_reporting(E_ALL);

/**
 *  
 * Backup SchuleIntern
 * 
 * Erzeugt ein Backup der SchuleIntern Instanz
 * 
 * @author Christian Marienfeld <post@chrisland.de>
 * @copyright 2019
 * @version 1.1
 * 
 * How To Use:
 * 
 * .../cli/MakeBackup.php
 *
 * METHOD:
 * ---------- SSH / cli
 *
 * $ php MakeBackup.php
 * $ php MakeBackup.php data
 * $ php MakeBackup.php full
 * $ php MakeBackup.php database
 *
 * ---------- Client / Get
 *
 * Parameter: action[GET]
 * 
 * .../cli/MakeBackup.php
 * .../cli/MakeBackup.php?action=data
 * sql dump & data folder
 * 
 * .../cli/MakeBackup.php?action=full
 * sql dump & (data,framework,cli,www) folder
 * 
 * .../cli/MakeBackup.php?action=database
 * sql dump
 * 
 */

class Backup {

    private $debug = array();
    private $settings = false;
    private $filename = false;

    private $pathZip = false;
    private $fileZip = false;

    private $info = array();

    private $blacklistFolderPaths = array(
        '../data/backup',
        '../data/temp'
    );
    private $blacklistFolder = array(
        'node_modules'
    );
    private $blacklistFile = array(
        '.DS_Store'
    );

    public function getDebug() {
        return $this->debug || false;
    }
    
    public function setDebug($str) {
        if ($str) {
            array_push($this->debug, $str);
        }
    }

    public function execute() {

        include("vendor/autoload.php");
		
		if ($_SERVER['SSH_CONNECTION']) {
			// execute from cli/ssh
			$action = $_SERVER['argv'][1];
			echo("-------------------------------------\r\n");
			echo("SchuleIntern Backup....\r\n");
			echo("-------------------------------------\r\n");
			echo ("Bitte warten ....\r\n");
			echo ("(Dies kann einige Minuten dauern.)\r\n");
			
		} if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			// execute from client/get
			$action = $_GET['action'];
		}

        
        if (!$action) {
            $action = 'data';
        }

        if ( !$this->startBackup($action) ) {
            $this->exitAndEnd();
        }

        if ( !$this->getSettings() ) {
            $this->setDebug('ERROR: Settings konnten nicht geladen werden!');
            $this->exitAndEnd();
        }

        if ($action == 'data') {

            $backupDatabase = true;
            $backupFolders = ['data'];

        } else if ($action == 'full') {
            
            $backupDatabase = true;
            $backupFolders = ['cli','data','framework','www'];


        } else if ($action == 'database') {

            $backupDatabase = true;
            $backupFolders = false;

        } else {
            echo '- wrong action -';
            exit;
        }

        if ($backupDatabase) {
            if ( !$this->dbDump() ) {
                $this->setDebug('ERROR: Database Dump war nicht erfolgreich!');
                $this->exitAndEnd();
            }
        }

        if ($backupFolders != false) {
            if ( !$this->folderDump($backupFolders) ) {
                $this->setDebug("ERROR: Datensicherung war nicht erfolgreich!");
                $this->exitAndEnd();
            }
        }
        

        $this->setDebug("\r\nBackup erfolfolgreich. Datei: \"../data/backup/" . $this->filename . ".zip");

        $this->endBackup();

        exit();

    } 

    private function exitAndEnd() {

        $this->makeLog();
        exit(1);
    }

    private function startBackup($action) {

        $this->info['Action'] = $action;
        $this->info['StartTime'] = time();

        $this->filename = date( 'Y-m-d-His',time() ) .'_'. $this->info['Action'];

        $this->pathZip = "../data/backup/" . $this->filename . ".zip";

        $this->fileZip = new ZipArchive();

        if ($this->fileZip->open($this->pathZip, ZipArchive::CREATE) !== TRUE) {
            $this->setDebug('Error: cannot open <'.$this->pathZip.'>');
            return false;
        }

        include("../data/config/config.php");
        $this->settings = new GlobalSettings();

        return true;

    }

    private function endBackup() {

        $this->info['EndTime'] = time();

        $this->makeLog();
        $this->addFileToZip('../data/backup/Backup-Log.txt','Backup-Log.txt');

        $this->fileZip->addFromString("BackupInfo.json", json_encode($this->info) );
        $this->fileZip->close();

        $this->deleteDirectory('../data/backup/sql-dump');

        // Display Messages
        foreach($this->debug as $value) {

            if ($_SERVER['SSH_CONNECTION']) {
				// execute from cli/ssh
				echo ("$value\r\n");
			} if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				// execute from client/get
				echo $value.'<br>';
			}
        }

    }

    private function makeLog() {

        // Make Log File
        $logText = 'BACKUP SchuleIntern'.PHP_EOL.PHP_EOL.'Start Time: '.date('Y-m-d H:i',$this->info['StartTime']).PHP_EOL.'End Time: '.date('Y-m-d H:i',$this->info['EndTime']).PHP_EOL.'SoftwareVersion: '.$this->info['SoftwareVersion'].PHP_EOL.PHP_EOL;
        foreach($this->debug as $value) {
            $logText .= $value.PHP_EOL;
        }
        if ( file_put_contents("../data/backup/Backup-Log.txt", $logText) ) {
            return true;
        }
        return false;

    }

    private function addFileToZip($file, $rename) {

        $str = "Daten zur ZIP Datei hinzufügen... File: ".$rename;

        if ( $this->fileZip->addFile($file, $rename) ) {
            $this->setDebug($str." - Erfolgreich!");
        } else {
            return false;
        }
        return true;

    }


    private function dbDump() {

        $this->setDebug('Erstelle Backup der Datenbank nach backup/' . $this->filename . "_Database.sql");

        $list = array();

        $mysqli = new mysqli($this->settings->dbSettigns['host'], $this->settings->dbSettigns['user'], $this->settings->dbSettigns['password'], $this->settings->dbSettigns['database']);
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
            $this->exitAndEnd();
        }


        $result = $mysqli->query("SHOW TABLE STATUS");
        
        $tables = array();
        while($row = mysqli_fetch_array($result)) {
            /* We return the size in Kilobytes */
            $total_size = ($row[ "Data_length" ] + 
                        $row[ "Index_length" ]) / 1024;
            $tables[$row['Name']] = sprintf("%.2f", $total_size);
        }
        
        // SQL Dump größe auf ca 4Mb halten
        $temp = 0;
        $maxKb = 4000;
        $nr = 0;
        $arr = array();
        foreach($tables as $key => $table) {

            if ($key == 'messages_messages') {
                continue;
            }

            $temp += $table;

            if ($temp > $maxKb) {
                $nr++;
                $temp = 0;
            }
            if ( !is_array( $arr[$nr] ) ) {
                $arr[$nr] = array();
            }
            array_push($arr[$nr], $key);

        }


        mkdir('../data/backup/sql-dump/');

        foreach($arr as $key => $table) {

            if ( strpos((string)$table, 'messages_') ) {
                continue;
            }
            $file = '../data/backup/sql-dump/dump-'.$key.".sql";
          
            try {
                $dump = new \Ifsnop\Mysqldump\Mysqldump('mysql:host=' . $this->settings->dbSettigns['host']. ';dbname=' . $this->settings->dbSettigns['database']. '',
                    $this->settings->dbSettigns['user'],
                    $this->settings->dbSettigns['password'],
                    array(
                        'compress' => 'none', //\Ifsnop\Mysqldump\Mysqldump::GZIP,
                        'add-drop-table' => true,
                        //'no-data' => true,
                        'include-tables' => $table
                    )
                );
    
                $dump->start($file);

            } catch (\Exception $e) {
                $this->setDebug('Fehler bei der Sicherung der Datenbank: ' . $e->getMessage());
                $this->exitAndEnd();
            }

            if ( !$this->addFileToZip(
                $file,
                "sql-dump/dump_".$key.".sql"
                ) ) {
                $this->setDebug("ERROR: Datenbankbackup konnte nicht zur ZIP Datei hinzugefügt werden.");
                $this->exitAndEnd();
            }

        }
        
        $this->setDebug("Erstellen des Datenbankbackups fertig.");

        return true;
    } 

    public function deleteDirectory($dirPath) {

        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object !="..") {
                    if (filetype($dirPath . '/' . $object) == "dir") {
                    $this->deleteDirectory($dirPath . '/' . $object);
                    } else {
                    unlink($dirPath . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
            return true;
        }
        
    }
        
    private function folderDump($backupFolders) {

        $this->setDebug("Sichere Dateien...");

        for($i = 0; $i < sizeof($backupFolders); $i++) {
            $this->setDebug("Sichere Ordner \"" . $backupFolders[$i] . "\"...");
            $this->zipFolder( "../".$backupFolders[$i], $backupFolders[$i] );
        }
        $this->setDebug("Datensicherung erfolgreich!");
        return true;

    } 

    private function zipFolder($folder, $zipFolder) {

        $dir = opendir( $folder );

        while($file = readdir($dir)) {
            if($file == "." || $file == "..") continue;
            
            $filepath = $folder . "/" . $file;

            if ( is_dir($filepath) ) {

                if ( !in_array($file, $this->blacklistFolder) && !in_array($filepath, $this->blacklistFolderPaths) ) {

                    $this->fileZip->addEmptyDir($filepath);
                    $this->zipFolder( $filepath, $zipFolder. "/" . $file );
                }

            } else if ( is_file($filepath) ) {

                if ( !in_array($file, $this->blacklistFile) ) {
                    if ( !$this->addFileToZip(
                        $filepath,
                        $zipFolder . "/" . $file
                        ) ) {
                        $this->setDebug("ERROR: Datenbankbackup konnte nicht zur ZIP Datei hinzugefügt werden.");
                        $this->exitAndEnd();
                    }
                }

            }

        }

    }

    private function getSettings() {

        $mysqli = new mysqli($this->settings->dbSettigns['host'], $this->settings->dbSettigns['user'], $this->settings->dbSettigns['password'], $this->settings->dbSettigns['database']);

        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
            $this->exitAndEnd();
        }

        $queryResult = $mysqli->query("SELECT * FROM settings WHERE settingName='currentVersion'");
        $row = mysqli_fetch_assoc($queryResult);

        if (!$row['settingValue']) {
            return false;
        }

        $this->info['SoftwareVersion'] = $row['settingValue'];

        return true;

    }

}

$backup = new Backup;
$backup->execute();

