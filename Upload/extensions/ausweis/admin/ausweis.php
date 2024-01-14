<?php



class extAusweisAdminAusweis extends AbstractPage
{

	public static function getSiteDisplayName()
	{
		return '<i class="fa fa-address-card"></i> Ausweis - Liste';
	}

	public function __construct($request = [], $extension = [])
	{
		parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute()
	{

		//$this->getRequest();
		$acl = $this->getAcl();

		$user = DB::getSession()->getUser();

		if (!$this->canWrite()) {
			new errorPage('Kein Zugriff');
		}

		$this->render([
			"tmpl" => "default",
			"scripts" => [
				PATH_EXTENSION . 'tmpl/scripts/ausweis/dist/js/chunk-vendors.js',
				PATH_EXTENSION . 'tmpl/scripts/ausweis/dist/js/app.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"apiURL" => "rest.php/ausweis",
				"acl" => $acl['rights']
			]
		]);
	}

	public static function taskGetFile($request)
	{
		if ($request['path']) {
			$path = PATH_DATA . 'ext_ausweis' . DS . 'ausweis' . DS . $request['path'];
			echo FILE::getFile($path);
			exit;
		}
	}
}
