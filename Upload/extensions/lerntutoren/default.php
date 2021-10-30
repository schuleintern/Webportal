<?php

class lerntutorenDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Lerntutoren - Verfügbare Angebote';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

		//$this->getRequest();
		//$this->getAcl();


        include_once(PATH_EXTENSIONS.'lerntutoren/class/Tutor.class.php');
        include_once(PATH_EXTENSIONS.'lerntutoren/class/TutorSlot.class.php');

        $slotsHTML = "";


        $tutoren = Tutor::getAllByStatus('open');


        for ($i = 0; $i < sizeof($tutoren); $i++) {
            if ($tutoren[$i]->getEinheitenDiff() > 0) {

                $slotsHTML .= '
                <tr>
                    <td>
                        ' . $tutoren[$i]->getTutor()->getDisplayName();

                if ( $tutoren[$i]->getTutor()->isPupil() ) {
                    $slotsHTML .= ' ( '.$tutoren[$i]->getTutor()->getPupilObject()->getGrade().' )';
                } else if ( $tutoren[$i]->getTutor()->isTeacher() ) {
                    $slotsHTML .= ' ( Lehrer ) ';
                }
                $slotsHTML .= '</td>
                    <td>
                       ' . $tutoren[$i]->getJahrgang() . '
                    </td>
                    <td>
                       ' . $tutoren[$i]->getFach() . '
                    </td>
                    <td>
                       ' . $tutoren[$i]->getEinheitenDiff() . '
                    </td>
                    <td>
                        <a href="index.php?page=lerntutoren&view=show&id=' . $tutoren[$i]->getID() . '" class="btn btn-primary"><i class="fa fa-file"></i> Öffnen</a>
                    </td>
                 </tr>';
            }
        }


        $this->render([
			"tmpl" => "default",
            "var" => [
                [ "disclaimer", DB::getSettings()->getValue('tutoren-disclaimer') || false ],
                [ "slotsHTML" , $slotsHTML ]
            ]
		]);

	}


}
