<?php



class extFinanzenDefault extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-sun"></i> Finanzen';
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


        $bankEmpfaenger = DB::getSettings()->getValue('extFinanzen-bank-empfaenger');
        $bankName = DB::getSettings()->getValue('extFinanzen-bank-name');
        $bankIBAN = DB::getSettings()->getValue('extFinanzen-bank-iban');
        $bankBIC = DB::getSettings()->getValue('extFinanzen-bank-bic');


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/default/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/finanzen",
                "acl" => $acl['rights'],
                "bankEmpfaenger" => $bankEmpfaenger,
                "bankName" => $bankName,
                "bankIBAN" => $bankIBAN,
                "bankBIC" => $bankBIC
            ]
        ]);
    }


    public function taskTransfer()
    {



        /**
         * SAMPLE - Displays the current balance of all accounts.
         */

        // See login.php, it returns a FinTs instance that is already logged in.
        /** @var \Fhp\FinTs $fints */
        //$fints = require_once 'login.php';
        $fints = require_once  PATH_EXTENSIONS . 'finanzen'.DS.'fin'.DS.'login.php';


        // Just pick the first account for the request, though we will request the balance of all accounts.
        $getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
        $fints->execute($getSepaAccounts);
        if ($getSepaAccounts->needsTan()) {
            handleStrongAuthentication($getSepaAccounts); // See login.php for the implementation.
        }
        $oneAccount = $getSepaAccounts->getAccounts()[0];

        $getBalance = \Fhp\Action\GetBalance::create($oneAccount, true);
        $fints->execute($getBalance);
        if ($getBalance->needsTan()) {
            handleStrongAuthentication($getBalance); // See login.php for the implementation.
        }

        /** @var \Fhp\Segment\SAL\HISAL $hisal */
        foreach ($getBalance->getBalances() as $hisal) {
            $accNo = $hisal->getAccountInfo()->getAccountNumber();
            if ($hisal->getKontoproduktbezeichnung() !== null) {
                $accNo .= ' (' . $hisal->getKontoproduktbezeichnung() . ')';
            }
            $amnt = $hisal->getGebuchterSaldo()->getAmount();
            $curr = $hisal->getGebuchterSaldo()->getCurrency();
            $date = $hisal->getGebuchterSaldo()->getTimestamp()->format('Y-m-d');
            echo "On $accNo you have $amnt $curr as of $date.\n";
        }

        exit;

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        /** @noinspection PhpUnhandledExceptionInspection */

        /**
         * SAMPLE - Send a direct debit request
         *
         * Note: The phpFinTs library only implements the FinTS protocol. For SEPA transfers, you need a separate library to
         * produce the SEPA XML data, which is then wrapped into FinTS requests. This example uses the Sephpa library
         * (see https://github.com/AbcAeffchen/Sephpa), which you need to install separately.
         */

        // See login.php, it returns a FinTs instance that is already logged in.
        /** @var \Fhp\FinTs $fints */
        $fints = require_once  PATH_EXTENSIONS . 'finanzen'.DS.'fin'.DS.'login.php';

        // Just pick the first account, for demonstration purposes. You could also have the user choose, or have SEPAAccount
        // hard-coded and not call getSEPAAccounts() at all.
        $getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
        $fints->execute($getSepaAccounts);
        if ($getSepaAccounts->needsTan()) {
            handleStrongAuthentication($getSepaAccounts); // See login.php for the implementation.
        }
        $oneAccount = $getSepaAccounts->getAccounts()[0];

        // generate a SepaDirectDebit object (pain.008.003.02).
        $directDebitFile = new \AbcAeffchenSephpa\SephpaDirectDebit(
            'Name of Application',
            'Message Identifier',
            \AbcAeffchenSephpa\SephpaDirectDebit::SEPA_PAIN_008_003_02
        );
        /*
        *
        * Configure the Direct Debit File
        * $directDebitCollection = $directDebitFile->addCollection([...]);
        * $directDebitCollection->addPayment([...]);
        *
        * See documentation:
        * https://github.com/AbcAeffchen/Sephpa
        *
        */
        $xml = $directDebitFile->generateXml(date("Y-m-d\TH:i:s", time()));

        $sendSEPADirectDebit = \Fhp\Action\SendSEPADirectDebit::create($oneAccount, $xml);
        $fints->execute($sendSEPADirectDebit);
        if ($sendSEPADirectDebit->needsTan()) {
            handleStrongAuthentication($sendSEPADirectDebit); // See login.php for the implementation.
        }

        exit;

    }
}
