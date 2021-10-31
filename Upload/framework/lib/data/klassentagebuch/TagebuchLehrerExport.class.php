<?php

/**
 * Exportauftrag als PDF für einen Lehrer
 */
class TagebuchLehrerExport {

    /**
     * @var lehrer
     */
    private $teacher = null;

    /**
     * @param $teacher
     */
    public function __construct($teacher) {
        $this->teacher = $teacher;
    }

    /**
     * Generieren und ablegen.
     * Datei ID in Settings abspeichern.
     */
    public function generateAndSaveAsPDF() {
        $entries = TagebuchKlasseEntry::getAllForTeacher($this->teacher->getKuerzel());

        $html = "<h1>Lehrertagebuch Lehrkraft " . $this->teacher->getKuerzel() . " - Schuljahr " . DB::getSettings()->getValue("general-schuljahr") . "</h1>";

        $html .= "<table border=\"1\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">

                    <tr>
                        <td width=\"10%\"><b>Datum</b></td>
                        <td width=\"7%\"><b>Klasse</b></td>
                        <td width=\"7%\"><b>Fach</b></td>
                        <td width=\"7%\"><b>Stunde</b></td>
                        <td width=\"41%\"><b>Stoff</b></td>
                        <td width=\"21%\"><b>Hausaufgabe</b></td>
                        <td width=\"7%\"><b>Entfall?</b></td>
                    </tr>";

        for($i = 0; $i < sizeof($entries); $i++) {
            $html .= "<tr>";

            $html .= "<td>" . DateFunctions::getWeekDayNameFromNaturalDate(DateFunctions::getNaturalDateFromMySQLDate($entries[$i]->getDate()))
                 . "<br><b>" . DateFunctions::getNaturalDateFromMySQLDate($entries[$i]->getDate()) . "</b></td>";

            $html .= "<td>" . $entries[$i]->getGrade() . "</td>";
            $html .= "<td>" . $entries[$i]->getFach() . "</td>";
            $html .= "<td>" . $entries[$i]->getStunde() . "</td>";
            $html .= "<td>" . htmlspecialchars($entries[$i]->getStoff()) . "</td>";
            $html .= "<td>" . htmlspecialchars($entries[$i]->getHausaufgabe()) . "</td>";
            $html .= "<td>" . ($entries[$i]->isAusfall() ? "ja" : "-") . "</td>";



            $html .= "</tr>";
        }


        $html .= "</table>";

        $pdf = new PrintNormalPageA4WithHeader("Lehrertagebuch " . $this->teacher->getKuerzel(), "A4", "L");

        $pdf->setHTMLContent($html);
        $pdf->setPrintedDateInFooter();


        $upload = FileUpload::uploadFromTCPdf("Lehrertagebuch " . $this->teacher->getKuerzel() . ".pdf", $pdf);

        /** @var FileUpload $upload */
        $upload = $upload['uploadobject'];
        /**		return [
        'result' => true,
        'uploadobject' => new FileUpload($data),
        'mimeerror' => false,
        'text' => "Save from TCPDF OK"
        ];
         */

        DB::getSettings()->setValue("lehrertagebuch-export-" . $this->teacher->getAsvID(), $upload->getID());

        return true;

    }
}