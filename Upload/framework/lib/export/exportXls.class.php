<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 *
 * @author Christian M
 *
 */
class exportXls
{

    private $spreadsheet;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }

    public function getSheet()
    {
        return $this->spreadsheet;
    }

    public function setOptions($array)
    {
        $this->spreadsheet->getProperties()
            ->setCreator('Schule-Intern')
            ->setLastModifiedBy('Schule-Intern')
            ->setTitle('Schule-Intern')
            ->setSubject('Schule-Intern')
            ->setDescription('Schule-Intern')
            ->setKeywords('Schule-Intern')
            ->setCategory('Schule-Intern');

        if ($array['title']) {
            $this->spreadsheet->getProperties()->setTitle($array['title']);
        }
        if ($array['desc']) {
            $this->spreadsheet->getProperties()->setDescription($array['desc']);
        }
        if ($array['creator']) {
            $this->spreadsheet->getProperties()->setCreator($array['creator']);
        }
        if ($array['modifiedBy']) {
            $this->spreadsheet->getProperties()->setLastModifiedBy($array['modifiedBy']);
        }
    }

    public function output($filename = 'Datei.xls', $path = 'php://output')
    {
// Redirect output to a client’s web browser (Xls)
        // header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($this->spreadsheet, 'Xls');
        $writer->save('php://output');
    }


    /*
    public static function execute()
    {


// Set document properties


// Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Hello')
            ->setCellValue('B2', 'world!')
            ->setCellValue('C1', 'Hello')
            ->setCellValue('D2', 'world!');

// Miscellaneous glyphs, UTF-8
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A4', 'Miscellaneous glyphs')
            ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

// Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Simple');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="01simple.xls"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;

    }
*/

}


?>
