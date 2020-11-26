<?php

/**
 * Verwaltet die Crons.
 * @author Christian Spitschka
 * @version 1.0 Beta
 */
class cronhandler {
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
        'SprechtagVikoCreator'
	];
	
	
	public function __construct() {
		header("Content-type: application/json");

		error_reporting(E_ERROR);

		$jsonAntwort = [];
		
		if(DB::getGlobalSettings()->cronkey != $_REQUEST['cronkey']) {
			$jsonAntwort['error'] = true;
			$jsonAntwort['errorText'] = 'Invalid cronkey';

            http_response_code(403);
			echo(json_encode($jsonAntwort));
			exit(0);
		}
		
		PAGE::setFactory( new FACTORY() );
		
		//Check Access Key
		
		if(isset($_REQUEST['cronName']) && $_REQUEST['cronName'] != "") {
		    // Execute Single Cron
		    $cronName = $_REQUEST['cronName'];
		    
		    $result = [];

            $jsonAntwort = [];


            if(in_array($cronName, self::$allowedActions)) {
		        /**
		         * 
		         * @var AbstractCron $cron
		         */
		        $cron = new $cronName();
		        if(true || $cron->onlyExecuteSeparate()) {      // Jeder Cron sollte einzeln immer ausführbar sein.
		            
		            
		            
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
    							'" . ($result['success'] ? 1 : 0). "',
    							'" . DB::getDB()->escapeString($result['resultText']) . "'
    						)
    				");

		        }

                $jsonAntwort['cronResult'] = $result;
		        
		    }
            else {
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
		
		if($isRunning != 0) {
		    if(($isRunning + 1800) <= time() || isset($_REQUEST['resetCronStatus']) && $_REQUEST['resetCronStatus'] > 0) {
		        // Nach 30 Minuten Reset
		        DB::getSettings()->setValue("cronRunning", time());
		    }
		    else {
		        $jsonAntwort['error'] = true;
		        $jsonAntwort['errorText'] = 'Cron still running.';
		        echo(json_encode($jsonAntwort));
		        exit(0);
		    }
		}
		
		DB::getSettings()->setValue("cronRunning", time());

		$jsonAntwort['mode'] = 'multi_cron';

		$jsonAntwort['crons'] = [];

		//
		
		for($i = 0; $i < sizeof(self::$allowedActions); $i++) {
			require_once('../framework/lib/cron/'.self::$allowedActions[$i].".class.php");
		}
		
		// Remove Slashes in pages
		for($i = 0; $i < sizeof(self::$allowedActions); $i++) {
			if(strpos(self::$allowedActions[$i],"/") > 0) self::$allowedActions[$i] = substr(self::$allowedActions[$i], strpos(self::$allowedActions[$i],"/")+1);
		}
		
		
		for($i = 0; $i < sizeof(self::$allowedActions); $i++) {
			/**
			 * 
			 * @var AbstractCron $cron
			 */
			$cron = new self::$allowedActions[$i]();


			if(!$cron->onlyExecuteSeparate()) {


			
    			$lastExecution = DB::getDB()->query_first("SELECT * FROM cron_execution WHERE cronName='" . self::$allowedActions[$i] . "' ORDER BY cronStartTime DESC LIMIT 1");
    			
    			if(sizeof($lastExecution) == 0) {
    				$lastExecution = time() - 1 - $cron->executeEveryXSeconds();	// First Run
    			}
    			else $lastExecution = $lastExecution['cronStartTime'];

                $cronStatus = 'skipped';

                $result = null;

    			
    			$execute = false;
    			
    			if(($lastExecution + $cron->executeEveryXSeconds()) < time()) {
    				$execute = true;
    				$cronStatus = 'executed';
    			}
    			
    			if($execute) {
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
    							'" . self::$allowedActions[$i] . "',
    							'" . $startTime . "',
    							'" . $endTime . "',
    							'" . ($result['success'] ? 1 : 0). "',
    							'" . DB::getDB()->escapeString($result['resultText']) . "'
    						)
    				");
    			}

                $jsonAntwort['crons'][] = [
                    'name' => self::$allowedActions[$i],
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
	
	public static function getAllowedActions() {
		return self::$allowedActions;
	}
}

?>