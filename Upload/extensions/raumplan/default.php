<?php



class extRaumplanDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-door-open"></i> Raumplan';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

        $_request = $this->getRequest();
        $acl = $this->getAcl();


        //print_r($_request);
		//$this->getAcl();

        $settingsRooms = DB::getSettings()->getValue('raumplan-rooms');

        $getRoom = $_request['room'];
        if (!$getRoom) {
            $getRoom = json_decode($settingsRooms)[0];
        }
        if (!$getRoom) {

            if ((int)DB::getSession()->getUser()->isAnyAdmin() === 1) {
                header("Location: index.php?page=ext_raumplan&view=custom&admin=true");
            } else {
                new errorPage("Leider wurde kein Raum gewählt!");
            }
        }

        //echo $getRoom;


        $showDays = array(
            'Mo' => DB::getSettings()->getValue('extRaumplan-day-mo') || 0,
            'Di' => DB::getSettings()->getValue('extRaumplan-day-di') || 0,
            'Mi' => DB::getSettings()->getValue('extRaumplan-day-mi') || 0,
            'Do' => DB::getSettings()->getValue('extRaumplan-day-do') || 0,
            'Fr' => DB::getSettings()->getValue('extRaumplan-day-fr') || 0,
            'Sa' => DB::getSettings()->getValue('extRaumplan-day-sa') || 0,
            'So' => DB::getSettings()->getValue('extRaumplan-day-so') || 0,
        );


		$this->render([
			"tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION.'tmpl/scripts/default/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/raumplan",
                "showDays" => $showDays,
                "room" => $getRoom,
                "rooms" => $settingsRooms
		    ]
        ]);

	}

	/**
	 * Example Task Function
	 */
	public function taskPrint() {

		// Mach hier etwas cooles!!!

		$this->reloadWithoutParam('task');
	}

}
