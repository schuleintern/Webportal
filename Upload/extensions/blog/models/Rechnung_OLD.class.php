<?php

/**
 *
 */



class extFinanzenModelRechnung extends ExtensionModel
{


    static $table = 'ext_finanzen_rechnung';

    static $fields = [
        'state',
        'createdTime',
        'createdUserID',
        'user_id',
        'summe',
        'adresse',
        'orderNumber',
        'filePath',
        'buchung_ids'
    ];


    static $defaults = [
        'state' => 1,
        'user_id' => 0,
        'summe' => 0,
        'adresse' => '',
        'orderNumber' => 0,
        'filePath' => '',
        'buchung_ids' => ''
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false);
        self::setModelFields(self::$fields, self::$defaults);
    }

    public function getCollection($full = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getData('user_id')) {
                $temp_user = user::getUserByID($this->getData('user_id'));
                if ($temp_user) {
                    $collection['user'] = $temp_user->getCollection();
                }
            }
        }

        return $collection;
    }


    public function setPayed()
    {
        $this->setState(2);

        // Buchungen setState()
        if ($this->getData('buchung_ids')) {
            $buchungs = explode(',', $this->getData('buchung_ids') );
            if (count($buchungs) > 0) {
                include_once PATH_EXTENSION . 'models' . DS . 'Buchung.class.php';
                $class = new extFinanzenModelBuchung();
                foreach ($buchungs as $buchung_id) {
                    $class->getByID($buchung_id)->setState(3);
                }
            }
        }
        
    
    }


    public function makeRechnugen($userID = false, $orders = false)
    {

        if (!$userID) {
            return false;
        }
        if (!$orders || !is_array($orders)) {
            return false;
        }

        $path = PATH_DATA . 'ext_finanzen' . DS;
        if (!is_dir($path)) {
            mkdir($path);
        }
        $path = $path . 'rechnungen' . DS;
        if (!is_dir($path)) {
            mkdir($path);
        }

        $date = date('Y-m-d');



        $order_number = (int)DB::getSettings()->getValue('extFinanzen-antrag-order-number');
        if (!$order_number) {
            $order_number = 0;
        }
        $order_number++;

        $prefix = (string)DB::getSettings()->getValue('extFinanzen-antrag-order-number-prefix');
        if ($prefix == "") {
            $prefix = 'rn-si';
        }
        $order_number_string = $prefix . '-' . $order_number;


        $userObj = user::getUserByID($userID);
        if ($userObj) {

            $buchung_ids = [];
            $total = 0;
            foreach ($orders as $row) {
                $buchung_ids[] = $row->getID();
                $amount = $row->getData('quant') * $row->getData('amount');
                $total = $total + $amount;
            }


            if ($filepath = self::makePDF($userObj, $date, $order_number_string, $orders, $total)) {

                DB::getSettings()->setValue('extFinanzen-antrag-order-number', $order_number);

                $this->save([
                    'orderNumber' => $order_number_string,
                    'filePath' => $filepath,
                    'user_id' => $userObj->getUserID(),
                    'summe' => $total,
                    'adresse' => '',
                    'buchung_ids' => implode(',', $buchung_ids)
                ]);

                return true;
            }
        }



        return false;
    }

    public static function makePDF($userObj = false, $date = false, $order_number = false, $orders = false, $total = 0)
    {

        if (!$userObj || !$date || !$order_number || !$total) {
            return false;
        }
        if (!$orders || !is_array($orders)) {
            return false;
        }

        $path = PATH_DATA . 'ext_finanzen' . DS;
        if (!is_dir($path)) {
            mkdir($path);
        }
        $path = $path . 'rechnungen' . DS;
        if (!is_dir($path)) {
            mkdir($path);
        }



        $user_path = $path . 'user-' . $userObj->getUserID() . DS;
        if (!is_dir($user_path)) {
            mkdir($user_path);
        }
        $filename = $date . '-Rechnung';
        $pdf = new PrintNormalPageA4WithHeader($filename);
        $pdf->setPrintedDateInFooter();

        $pdf->SetFont("dejavusans", "", 9);

        // add a page
        $pdf->AddPage();

        // create address box
        $pdf->text(20, 55, 'Customer name Inc.');
        $pdf->text(20, 60, 'Mr. Tom Cat');
        $pdf->text(20, 65, 'Street address');
        $pdf->text(20, 70, 'Zip, city name');

        // invoice title / number
        $pdf->SetFont("dejavusans", "B", 12);
        $pdf->text(20, 90, 'Rechnung');

        $pdf->SetFont("dejavusans", "", 9);

        // date, order ref
        $pdf->text(150, 90, 'Datum: ' . $date);
        $pdf->text(150, 95, 'Nummer: ' .  $order_number);

        $pdf->SetFont("dejavusans", "B", 9);
        // list headers
        $pdf->text(20, 120, 'Anzahl');
        $pdf->text(40, 120, 'Produkt/Dienstleistung');
        $pdf->text(150, 120, 'Preis');
        $pdf->text(170, 120, 'Summe');

        $pdf->SetFont("dejavusans", "", 9);

        $pdf->Line(20, 129, 195, 129);

        $currY = 135;

        foreach ($orders as $row) {
            $pdf->text(20, $currY, $row->getData('quant'));
            $pdf->text(40, $currY, $row->getData('title'));
            $pdf->text(150, $currY, $row->getData('amount') . ' €');
            $amount = $row->getData('quant') * $row->getData('amount');
            $pdf->text(170, $currY,  $amount . ' €');
            $currY = $currY + 5;
        }
        $pdf->Line(20, $currY + 4, 195, $currY + 4);

        $pdf->SetFont("dejavusans", "B", 11);

        $pdf->text(140, $currY + 5, 'Summe: ');
        $pdf->text(170, $currY + 5, number_format($total, 2, '.', '') . ' €');

        // some payment instructions or information
        $pdf->setXY(20, $currY + 30);
        $pdf->SetFont("dejavusans", "", 9);

        $text = (string)nl2br(DB::getSettings()->getValue('extFinanzen-antrag-order-text-after'));
        if ($text) {
            $pdf->MultiCell(175, 10, $text, 0, 'L', 0, 1, '', '', true, null, true);
        }
        //$pdf->Output($user_path.$filename.'.pdf','F');
        $pdf_string = $pdf->Output('pseudo.pdf', 'S');
        if (file_put_contents($user_path . $filename . '.pdf', $pdf_string)) {
            return $user_path . $filename . '.pdf';
        }
        return false;
    }
}
