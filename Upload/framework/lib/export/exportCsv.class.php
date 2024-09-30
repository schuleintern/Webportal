<?php


/**
 *
 * @author Christian M
 *
 */
class exportCsv
{

    private $file = false;

    private $spreadsheet;

    public function __construct($filename = 'Datei.csv')
    {
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        header('Content-Type: text/csv');
    }

    public function getSheet()
    {


        $this->file = fopen('php://output', 'wb');

        if ($this->file) {
            return $this->file;
        }

        return false;
    }

    public function setOptions($array)
    {

    }

    public function output($filename = 'Datei.csv', $path = 'php://output')
    {

        header('Content-Disposition: attachment; filename="'.$filename.'";');
        fclose($this->file);
    }



}


?>
