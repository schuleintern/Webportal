<?php

class lerntutorenMyslots extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Lerntutoren - Meine Angebote';
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

        $mySlotsHTML = '';

        $selfUser = DB::getSession()->getUser();
        $myTutoren = Tutor::getByTutor($selfUser);
        if ($myTutoren) {
            $isTutor = true;
            //dump($myTutoren);

            for ($i = 0; $i < sizeof($myTutoren); $i++) {
                //dump($myTutoren[$i]->getSchueler());


                $mySlotsHTML .= '
                <tr>
                    <td>
                       ' . $myTutoren[$i]->getStatusNice() . '
                    </td>
                    <td>
                       ' . $myTutoren[$i]->getJahrgang() . '
                    </td>
                    <td>
                       ' . $myTutoren[$i]->getFach() . '
                    </td>
                    <td>
                       ' . $myTutoren[$i]->getEinheitenDiff() . ' ( von ' . $myTutoren[$i]->getEinheiten() . ' ) 
                    </td>
                    <td>';
                if ( $myTutoren[$i]->getStatus() == 'open' ) {
                    $mySlotsHTML .= '<a class="closeBtn_' . $myTutoren[$i]->getID() . ' btn btn-grau margin-r-l"><i class="fas fa-power-off"></i> Abbrechen</a>';
                    $mySlotsHTML .= '<a id="closeBtn_' . $myTutoren[$i]->getID() . '" href="index.php?page=Tutoren&mode=closeTutor&id=' . $myTutoren[$i]->getID() . '" class="btn btn-red margin-r-l" style="display:none"><i class="fas fa-power-off"></i> Endgültig Beenden</a>';
                    $mySlotsHTML .= '<script>
                    jQuery(".closeBtn_'.$myTutoren[$i]->getID().'").on("click", function (e) {
                        jQuery(e.currentTarget).hide();
                        jQuery("#closeBtn_'.$myTutoren[$i]->getID().'").show();
                    });
                    </script>';
                }

                $mySlotsHTML .= '</td>
                 </tr>';
                if ($myTutoren[$i]->getSlots()) {


                    $mySlotsHTML .= '<tr ><td colspan="5" ><div class="bg-white margin-l-l padding-l margin-b-m">';

                    $mySlotsHTML .= '<table class="table table-striped"><tr>
                    <th>Status</th>
                    <th>Schueler</th>
                    <th>Einheiten</th>
                    <th></th>
                </tr>';
                    for ($j = 0; $j < sizeof($myTutoren[$i]->getSlots()); $j++) {

                        $grade = '';
                        $name = $myTutoren[$i]->getSlots()[$j]->getSchueler()->getDisplayName();
                        if ( $myTutoren[$i]->getSlots()[$j]->getSchueler()->isPupil() ) {
                            $name = $myTutoren[$i]->getSlots()[$j]->getSchueler()->getPupilObject()->getRufname();
                            $grade = ' ( '.$myTutoren[$i]->getSlots()[$j]->getSchueler()->getPupilObject()->getGrade().' )';
                        }


                        $mySlotsHTML .= '<tr>
                                    <td>' . $myTutoren[$i]->getSlots()[$j]->getStatusNice() . '</td>
                                    <td>' . $myTutoren[$i]->getSlots()[$j]->getSchueler()->getDisplayName() . $grade.'</td>
                                    <td>' . $myTutoren[$i]->getSlots()[$j]->getEinheiten() . '</td>
                                    <td>
                                        <a href="index.php?page=MessageCompose&recipient=U:' . $myTutoren[$i]->getSlots()[$j]->getSchueler()->getUserID() . '" class="btn btn-primary margin-r-l"><i class="fa fa-envelope"></i> Nachricht an '.$name.'</a>';
                        if ($myTutoren[$i]->getSlots()[$j]->getStatus() != 'close') {
                            $mySlotsHTML .= '<a href="index.php?page=Tutoren&mode=closeForm&id=' . $myTutoren[$i]->getSlots()[$j]->getID() . '" class="btn btn-gruen"><i class="fa fa-user-check"></i> Final Abschließen</a>';
                        }

                        $mySlotsHTML .= '</td>
                                    </tr>';
                    }
                    $mySlotsHTML .= '</table>';
                    $mySlotsHTML .= '</div></td></tr>';
                }
            }
        }

        $mySlots = TutorSlot::getByUser($selfUser);

        //dump($mySlots);

        if ($mySlots) {
            $hasSlots = true;

            for ($i = 0; $i < sizeof($mySlots); $i++) {

                $tutor = Tutor::getByID($mySlots[$i]->getTutorenID());

                $grade = '';
                $name = $tutor->getTutor()->getDisplayName();
                if ( $tutor->getTutor()->isPupil() ) {
                    $name = $tutor->getTutor()->getPupilObject()->getRufname();
                    $grade = ' ( '.$tutor->getTutor()->getPupilObject()->getGrade().' )';
                }

                $myBookedSlotsHTML .= '
                <tr>
                    <td>
                       ' . $mySlots[$i]->getStatusNice() . '
                    </td>
                    <td>
                       ' . $mySlots[$i]->getEinheiten() . '
                    </td>
                    <td>
                       ' . $tutor->getFach() . '
                    </td>
                    <td>
                       ' . $tutor->getJahrgang() . '
                    </td>
                    <td>
                       ' . $tutor->getTutor()->getDisplayName() . $grade . '
                    </td>
                    <td>';



                $myBookedSlotsHTML .= '<a href="index.php?page=MessageCompose&recipient=U:' . $tutor->getTutor()->getUserID() . '" class="btn btn-primary margin-r-l"><i class="fa fa-envelope"></i> Nachricht an '.$name.'</a>
                    </td>
                    
                 </tr>';
            }
        }


        $this->render([
			"tmpl" => "myslots",
            "var" => [
                [ "HTML_tutoren_myslots_hinweis", DB::getSettings()->getValue('tutoren-myslots-hinweis') || false ],
                [ "mySlotsHTML" , $mySlotsHTML ],
                [ "isTutor" , $isTutor ],
                [ "hasSlots" , $hasSlots ],
                [ "myBookedSlotsHTML" , $myBookedSlotsHTML ]
            ]
		]);

	}


}
