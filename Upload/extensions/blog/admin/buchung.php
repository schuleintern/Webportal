<?php



class extFinanzenAdminBuchung extends AbstractPage
{

	public static function getSiteDisplayName()
	{
		return '<i class="fas fa-user-shield"></i> Finanzen - Buchungen';
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

		if (!$this->canAdmin()) {
			new errorPage('Kein Zugriff');
		}

		$this->render([
			"tmpl" => "default",
			"scripts" => [
				PATH_EXTENSION . 'tmpl/scripts/buchung/dist/js/chunk-vendors.js',
				PATH_EXTENSION . 'tmpl/scripts/buchung/dist/js/app.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"apiURL" => "rest.php/finanzen",
				"acl" => $acl['rights']
			]
		]);
	}
}
