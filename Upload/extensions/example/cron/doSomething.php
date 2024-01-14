<?php

class extExampleCronDoSomething extends AbstractCron {

    public function __construct() {

    }

    public function execute()
    {

        //echo 'JOP !';
    }


    public function getName() {
        return "Example Cron";
    }

    public function getDescription() {
        return "just the example - did nothing";
    }


    public function getCronResult() {
        return ['success' => 1, 'resultText' => 'Erfolgreich'];
    }

    public function informAdminIfFail() {
        return false;
    }

    public function executeEveryXSeconds() {
        return 2;		// Alle 2 Wochen ausführen.
    }


}