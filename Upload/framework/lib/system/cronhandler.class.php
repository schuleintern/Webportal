<?php

/**
 * Verwaltet die Crons.
 * @author Christian Spitschka
 * @version 1.0 Beta
 */
class cronhandler
{
    private static $allowedActions = [
        "CreateElternUsers",
        "SyncUsers",
        'ElternMailSenderCron',
        'MailSender',
        'ElternMailReceiver',
        'CreateDemoVplan',
        'UpdateExterneKalender',
        'CreateOffice365Users',
        'TagebuchFehlSucher',
        'FerienDownloader',
        'CronGarbageCollector',
        'CronNextCloud',
        'CronVerspaetungAuswertung',
        'CreateTagebuchPDFs',
        'DeleteOldElternUser',
        'MailSendDeleter',
        'CronStatMaker',
        'SprechtagVikoCreator',
        'LehrertagebuchExporter',
        'AllInOneKalenderFerien'
    ];


    public function __construct()
    {
        header("Content-type: application/json");

        error_reporting(E_ERROR);

        $jsonAntwort = [];

        if (DB::getGlobalSettings()->cronkey != $_REQUEST['cronkey']) {
            $jsonAntwort['error'] = true;
            $jsonAntwort['errorText'] = 'Invalid cronkey';

            http_response_code(403);
            echo(json_encode($jsonAntwort));
            exit(0);
        }

        include(PATH_PAGE."abstractPage.class.php");

        PAGE::setFactory(new FACTORY());


        $allowedActions = self::getAllowedActions();

        //Check Access Key

        if (isset($_REQUEST['cronName']) && $_REQUEST['cronName'] != "") {
            // Execute Single Cron
            $cronName = $_REQUEST['cronName'];

            $result = [];

            $jsonAntwort = [];


            if (in_array($cronName, $allowedActions)) {
                /**
                 *
                 * @var AbstractCron $cron
                 */
                $cron = new $cronName();
                if (true || $cron->onlyExecuteSeparate()) {      // Jeder Cron sollte einzeln immer ausführbar sein.


                    $startTime = time();
                    $cron->execute();
                    $endTime = time();

                    $result = $cron->getCronResult();

                    DB::getDB()->query("INSERT INTO cron_execution (
    						cronName,
    						cronStartTime,
    						cronEndTime,
    						cronSuccess,
    						cronResult)
    						values(
    							'" . $cronName . "',
    							'" . $startTime . "',
    							'" . $endTime . "',
    							'" . ($result['success'] ? 1 : 0) . "',
    							'" . DB::getDB()->escapeString($result['resultText']) . "'
    						)
    				");

                }

                $jsonAntwort['cronResult'] = $result;

            } else {
                $jsonAntwort['error'] = true;
                $jsonAntwort['errorText'] = $cronName . ' unknown';
                http_response_code(400);
            }

            $jsonAntwort['mode'] = 'single_cron';
            $jsonAntwort['cronName'] = $cronName;

            echo(json_encode($jsonAntwort));
            exit(0);


        }


        // Cron Running?

        $isRunning = DB::getSettings()->getValue("cronRunning");
        if ($isRunning != 0) {
            if (($isRunning + 1800) <= time() || isset($_REQUEST['resetCronStatus']) && $_REQUEST['resetCronStatus'] > 0) {
                // Nach 30 Minuten Reset
                DB::getSettings()->setValue("cronRunning", time());
            } else {
                $jsonAntwort['error'] = true;
                $jsonAntwort['errorText'] = 'Cron still running.';
                echo(json_encode($jsonAntwort));
                exit(0);
            }
        }

        DB::getSettings()->setValue("cronRunning", time());

        $jsonAntwort['mode'] = 'multi_cron';

        $jsonAntwort['crons'] = [];

        $cronList = [];

        for ($i = 0; $i < sizeof($allowedActions); $i++) {

            if (file_exists('../framework/lib/cron/' . $allowedActions[$i] . ".class.php")) {
                // Import default Class
                require_once('../framework/lib/cron/' . $allowedActions[$i] . ".class.php");

                // Remove Slashes in pages
                if (strpos($allowedActions[$i], "/") > 0) {
                    $cronList[$i] = substr($allowedActions[$i], strpos($allowedActions[$i], "/") + 1);
                } else {
                    $cronList[$i] = $allowedActions[$i];
                }
            } else if ( strpos($allowedActions[$i], 'ext') == 0 ) {
                // Extensions Cron was imported @ getAllowedActions()
                $cronList[$i] = $allowedActions[$i];
            }
            
        }
        
        

        for ($i = 0; $i < sizeof($cronList); $i++) {


            /**
             *
             * @var AbstractCron $cron
             */
            $cron = new $cronList[$i]();


            if (!$cron->onlyExecuteSeparate()) {


                $lastExecution = DB::getDB()->query_first("SELECT * FROM cron_execution WHERE cronName='" . $cronList[$i] . "' ORDER BY cronStartTime DESC LIMIT 1");


                if (sizeof($lastExecution) == 0) {
                    $lastExecution = time() - 1 - $cron->executeEveryXSeconds();    // First Run
                } else $lastExecution = $lastExecution['cronStartTime'];

                $cronStatus = 'skipped';

                $result = null;


                $execute = false;

                if (($lastExecution + $cron->executeEveryXSeconds()) < time()) {
                    $execute = true;
                    $cronStatus = 'executed';
                }

                if ($execute) {
                    $startTime = time();
                    $cron->execute();
                    $endTime = time();

                    $result = $cron->getCronResult();

                    DB::getDB()->query("INSERT INTO cron_execution (
    						cronName,
    						cronStartTime,
    						cronEndTime,
    						cronSuccess,
    						cronResult) 
    						values(
    							'" . $cronList[$i] . "',
    							'" . $startTime . "',
    							'" . $endTime . "',
    							'" . ($result['success'] ? 1 : 0) . "',
    							'" . DB::getDB()->escapeString($result['resultText']) . "'
    						)
    				");
                }

                $jsonAntwort['crons'][] = [
                    'name' => $cronList[$i],
                    'status' => $cronStatus,
                    'lastExecution' => $lastExecution,
                    'result' => $result
                ];
            }
        }

        // Cron zu Ende
        DB::getSettings()->setValue("cronRunning", 0);


        echo(json_encode($jsonAntwort));

        exit(0);

    }

    public static function getAllowedActions()
    {


        // Import Extensions Crons
        $extensions = DB::getDB()->query('SELECT `name`,`folder`, id FROM `extensions` WHERE active = 1 ');
        while($ext = DB::getDB()->fetch_array($extensions)) {
            if ($ext['folder']) {
                $path = PATH_EXTENSIONS.$ext['folder'].DS;
                if (file_exists($path.'extension.json')) {
                    $json = FILE::getExtensionJSON($path.'extension.json');
                    if ( isset($json['cron']) && count($json['cron']) > 0 ) {
                        foreach($json['cron'] as $cronExt) {
                            $remove = 'ext'.ucfirst($ext['folder']).'Cron';
                            $filename = lcfirst(str_replace($remove, '', $cronExt->class));
                            $classPath = $path.'cron'.DS.$filename.'.php';
                            if (file_exists($classPath)) {
                                require_once($classPath);
                                array_push(self::$allowedActions, $cronExt->class);
                            }
                        }
                    }
                }
            }
        }
        
        return self::$allowedActions;
    }
}

?>