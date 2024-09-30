<?php

class extStundenplanDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fa-table"></i> Stundenplan';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        $acl = $this->getAcl();
        if (!$this->canRead()) {
            new errorPage('Kein Zugriff');
        }

        $image_file = PAGE::logo();

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/stundenplan",
                "acl" => $acl['rights'],
                "isMobile" => $this->isMobile,
                "apiKey" => DB::getGlobalSettings()->apiKey,
                "stundeLabel" => DB::getSettings()->getValue("ext-stundenplan-stunde-label"),
                "showVplanBtn" => DB::getSettings()->getValue("ext-stundenplan-vplanBtn"),
                "showPrintBtn" => DB::getSettings()->getValue("ext-stundenplan-printBtn"),
                "printLogo" => $image_file,
                "printSystem" => DB::getGlobalSettings()->siteNamePlain,
                "printDate" => date('d.m.Y H:i', time())
            ]
        ]);
    }

    public function taskPrint($postData)
    {

        $key = (string)$_GET['key'];
        if (!$key) {
            exit;
        }
        $value = (string)$_GET['value'];
        if (!$value) {
            exit;
        }

        $currentPlan = stundenplandata::getCurrentStundenplan();
        $plan = $currentPlan->getPlan([$key, $value]);

        $arr = [];
        foreach ($plan as $d => $day) {

            foreach ($day as $s => $stunde) {
                if (!is_array($arr[$s])) {
                    $arr[$s] = [];
                }
                if (!is_array($arr[$s][$d])) {
                    $arr[$s][$d] = [];
                }
                $arr[$s][$d] = $stunde;
            }
        }

        $stylesheet2 = '<style>

.mainTable { width: 100vw; height: 100vh; }
.mainTable th, .mainTable td { width: 20vw; }
.stunde {width: 5vw;}
.stundeBox {

}
.stundeBox tr td{

}
.hourBox {
background-color: red !important;

}
.stundeBox  {

}

.col6 {width:30%;float:left;}
.col12 {width:100%;float:left;}

</style>';
        $html = '<table class="mainTable si-table" border="0" ><tbody>';


        foreach ($arr as $d => $day) {
            $html .= '<tr>';
            $html .= '<td class="stunde">#' . $d . '</td>';
            foreach ($day as $s => $stunden) {
                $html .= '<td class="">';

                $html .= '<div class="col12" >';
                foreach ($stunden as $stunde) {

                    $html .= '<div class="hourBox col6" >';

                    $html .= '<div>';
                    $html .= '<span class="stundeBox">' . $stunde['subject'] . '-</span>';
                    $html .= '<span class="stundeBox">' . $stunde['grade'] . '</span>';
                    $html .= '</div>';
                    $html .= '<div>';
                    $html .= '<span class="stundeBox">' . $stunde['teacher'] . '-</span>';
                    $html .= '<span class="stundeBox">' . $stunde['room'] . '</span>';
                    $html .= '</div>';

                    $html .= '</div>';

                    /*
                    $html .= '<table class="stundeBox" >';

                    $html .= '<tr>';
                    $html .= '<td >';
                    $html .= $stunde['subject'];
                    $html .= '</td>';

                    $html .= '<td>';
                    $html .= $stunde['grade'];
                    $html .= '</td>';
                    $html .= '</tr>';

                    $html .= '<tr>';
                    $html .= '<td>';
                    $html .= $stunde['teacher'];
                    $html .= '</td>';

                    $html .= '<td>';
                    $html .= $stunde['room'];
                    $html .= '</td>';
                    $html .= '</tr>';


                    $html .= '</table>';
                    */


                }
                $html .= '</div>';
                $html .= '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        //$html = '<div>'.$html.'</div>';

        //echo $stylesheet2; echo $html; exit;


        try {


            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'orientation' => 'L'
            ]);
            //$mpdf->debug = true;
            $stylesheet1 = file_get_contents('cssjs/css/si-components.css');
            $mpdf->WriteHTML($stylesheet1, 1);
            $mpdf->WriteHTML($stylesheet2, 1);
            $mpdf->WriteHTML($html, 2);
            $mpdf->Output();


        } catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception
            //       name used for catch
            // Process the exception, log, print etc.
            echo $e->getMessage();
        }

        exit;

        /*
        $print = new PrintNormalPageA4WithoutHeader("Stundenplan_" . $value . ".pdf", 'A4', 'L');
        //$print->setHTMLContent($html);

        $print->AddPage();
        $print->SetFont("dejavusans","",9);
        $print->writeHTML($html, true, false, false, false, '');
        $print->send();
*/
        //echo $html;


    }
}
