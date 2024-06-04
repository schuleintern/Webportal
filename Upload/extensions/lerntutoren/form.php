<?php



class extLerntutorenForm extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fa-plus-circle"></i> Lernangebot hinzufügen';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {


		$this->render([
			"tmpl" => "form",
            "vars" => [
                "LANG_add_text" => nl2br(DB::getSettings()->getValue("ext-lerntutoren-index-add-text")),
                "LANG_form_text" => nl2br(DB::getSettings()->getValue("ext-lerntutoren-form-text"))
            ]
		]);

	}

	/**
	 * Save new Tutor
	 */
	public function taskSave($data) {


        /*echo '<pre>';
        print_r($data);
        echo '</pre>';*/

        $userAsvID = DB::getSession()->getUser();

        if ($data['fach'] == ''
            || $data['jahrgang'] == ''
            || $data['einheiten'] == ''
            || $userAsvID->getData('userAsvID') == ''
            || !$userAsvID->getData('userAsvID')) {

            new errorPage('Missing Data');
            exit;
        }

        if ( DB::getDB()->query("INSERT INTO tutoren
                (
                    status,
                    created,
                    tutorenTutorAsvID,
                    fach,
                    jahrgang,
                    einheiten
                )
                values(
                       'created',
                       '" . date("Y-m-d H:i:s") . "',
                    '" . $userAsvID->getData('userAsvID') . "',
                    '" . DB::getDB()->escapeString($data['fach']) . "',
                    '" . DB::getDB()->escapeString($data['jahrgang']) . "',
                    " . (int)DB::getDB()->escapeString($data['einheiten']) . "
                )") ) {
            //$this->reloadWithoutParam('task');
            header("Location: index.php?page=ext_lerntutoren");
        }

        new errorPage('Error');
        exit;


	}

}
