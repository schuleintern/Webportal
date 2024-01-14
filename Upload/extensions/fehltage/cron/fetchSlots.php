<?php

class extFehltageCronFetchSlots extends AbstractCron
{

    public function __construct()
    {

    }

    public function execute()
    {


        include_once PATH_EXTENSIONS . 'fehltage' . DS . 'models' . DS . 'Items.class.php';
        //include_once PATH_EXTENSIONS . 'fehltage' . DS . 'models' . DS . 'Slots.class.php';
        //$slots = extFehltageModelSlots::getAll();

        //extFehltageModelItems::deleteALL();

        //$ret = [];
        $alleSchueler = schueler::getAll();


        //$alleSchueler = array_slice($alleSchueler, 0, 30);

        //include_once PATH_LIB .'data'.DS.'absenzen'.DS.'AbsenzenCalculator.class.php';
        //include_once PATH_LIB .'data'.DS.'absenzen'.DS.'Absenz.class.php';

        extFehltageModelItems::deleteALL();

        foreach ($alleSchueler as $schueler) {

            if ($schueler) {
                $absenzen = Absenz::getAbsenzenForSchueler($schueler);
 

                if ($absenzen) {

                    $absenzenCalculator = new AbsenzenCalculator($absenzen);

                    if ($absenzenCalculator) {


                        $absenzenCalculator->calculate();

                        //$collection = $schueler->getCollection();
                        //$absenzenStat = $absenzenCalculator->getDayStat();
                        $total = $absenzenCalculator->getTotal();
                        //$collection['start'] = $absenzenCalculator->getDayStat();
                        //$collection['total'] = $absenzenCalculator->getTotal();

                        

                        $foo = [
                            'user_id' => $schueler->getUserID(),
                            'total' => $total
                        ];

                        extFehltageModelItems::submit($foo); 

                        /*
                        $foo = [
                            'user_id' => $schueler->getUserID(),
                            'total' => $absenzenCalculator->getTotal()
                        ];
                        foreach ($slots as $slot) {

                            if ( (int)$slot->getTage() <= (int)$absenzenCalculator->getTotal() ) {

                                if ($foo['tage'] < $slot->getTage()) {

                                    $foo['tage'] = $slot->getTage();
                                    $foo['slot_id'] = $slot->getID();

                                    // keine doppelten
                                    if ( !extFehltageModelItems::getByUserAndSlot($foo['user_id'], $foo['slot_id'])) {
                                        extFehltageModelItems::submit($foo);
                                    }


                                }


                            }
                        }
                        */
                    }

                }

            }
        }



        return true;
    }


    public function getName()
    {
        return "Fehltage Fetch Slots";
    }

    public function getDescription()
    {
        return "";
    }


    public function getCronResult()
    {
        return ['success' => 1, 'resultText' => 'Erfolgreich'];
    }

    public function informAdminIfFail()
    {
        return false;
    }

    public function executeEveryXSeconds()
    {
        return 86400;        // 1 mal am tag
    }


}