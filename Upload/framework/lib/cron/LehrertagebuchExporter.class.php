<?php

class LehrertagebuchExporter extends AbstractCron {

    /**
     * @var String[]
     */
    private $log = [];

    public function execute()
    {
        /** @var lehrer[] $alleLehrer */
        $alleLehrer = lehrer::getAll();

        $done = 0;

        for($i = 0; $i < sizeof($alleLehrer); $i++) {

            if(DB::getSettings()->getBoolean("lehrertagebuch-export-antrag-" . $alleLehrer[$i]->getAsvID())) {
                $creator = new TagebuchLehrerExport($alleLehrer[$i]);
                $creator->generateAndSaveAsPDF();
                DB::getSettings()->setValue("lehrertagebuch-export-antrag-" . $alleLehrer[$i]->getAsvID(),0);
                $this->log[] = "Tagebuch für " . $alleLehrer[$i]->getDisplayNameMitAmtsbezeichnung() . " erstellt.";

                $done++;

                if($done == 2) break;

            }


        }

    }

    public function getName()
    {
        return "Lehrertagebücher Export";
    }

    public function getDescription()
    {
        return "Lehrertagebücher Export PDFs erstellen";
    }

    public function getCronResult()
    {
        return [
            'success' => true,
            'resultText' => implode("\n", $this->log)
        ];
    }

    public function informAdminIfFail()
    {
        return false;
    }

    public function executeEveryXSeconds()
    {
        return 180;
    }
}