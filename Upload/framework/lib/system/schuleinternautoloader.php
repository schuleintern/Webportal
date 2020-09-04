<?php 

$classes = [
	'abstractPages' => [
		'AbstractKalenderPage'
	],
    'externalRESTapi' => [
        'ExternalPortalRESTapi'
    ],
    'rest' => [
        'SimpleRestClient'
    ],
	'ajax' => [
		'ajax',
		'showHideElements',
	],
	'api' => [
		'AbstractApi',
		'GetSchoolData',
		'SingleSignOn',
	],
    
    'ausweise' => [
        'AbstractAusweis',
        'Schulausweis',
        'SchulausweisTCPDF'
    ],
    
	'captcha' => [
		'MathCaptcha',
	],
    'office365' => [
        'Office365Api',
        'Office365Login'
    ],
    'data/stundenplan' => [
      'Aufsicht'  
    ],
    'data/wlan' => [
        'WLanTicket'
    ],
	'cron' => [
		'AbstractCron',
		'CreateElternUsers',
		'ElternMailReceiver',
		'ElternMailSenderCron',
		'MailSender',
		'SyncUsers',
		'CreateDemoVplan',
		'UpdateExterneKalender',
	    'CreateOffice365Users',
	    'HeartbeatToManagementPortal',
	    'TagebuchFehlSucher',
	    'FerienDownloader',
	    'CronGarbageCollector',
	    'CronNextCloud',
	    'CronVerspaetungAuswertung',
	    'CreateTagebuchPDFs',
	    // 'CronAllInklMail',
        'DeleteOldElternUser'
	],
	'data/schulbuch' => [
		'BuchAusleihe',
		'Exemplar',
		'Schulbuch'
	],
	'ical' => ['iCalFile', 'MyIcalSettings'],
	'sms' => ['sms'],
	'data' => [
		'absenzen',
		'eltern',
		'elternmail',
		'Ferien',
		'grade',
		'klasse',
		'lehrer',
		'MatchUsersFunctions',
		'pupil',
		'schueler',
		'SchuelerAdresse',
		'SchuelerTelefonnummer',
		'SchuelerElternEmail',
		'session',
		'stundenplandata',
		'MatchUserFunctions',
		'amtsbezeichnung',
		'fach',
		'Schule',
	    'Fremdlogin'
	],
    'data/absenzen' => [
        'AbsenzenCalculator',
        'AbsenzBeurlaubungAntrag',
        'AbsenzVerspaetung',
        'AbsenzEntschuldigungGenerator',
        'AbsenzEntschuldigung',
        'AbsenzSchuelerInfo'
    ],    
    'data/respizienz' => [
        'LeistungsnachweisRespizienz'
    ],
	'data/termine' => [
		'Leistungsnachweis',
		'Klassentermin',
		'AbstractTermin',
		'Schultermin',
		'Lehrertermin',
		'ExtKalenderTermin',
	    'AndererKalenderTermin',
	    'KalenderKategorie',
	    'TerminCollector',
	    'ICSFeed',
        'AbstractKalenderKategorie',
        'ExternerKalenderKategorie'
	],
    'data/lerntutoren' => [
        'Lerntutor',
        'LerntutorSlot'
    ],
	'data/user' => [
		'user',
		'usergroup'
	],
	'data/dokumente' => [
		'dokumenteKategorie',
		'dokumenteGruppe',
		'dokumenteDokument'
	],
	'data/trenn' => [
		'TextTrenner'
	],
	'data/vplan' => [
		'TIMEUpdate'
	],
	'db' => [
		'mysql',
		'mysqldevdatabases'
	],
	'email' => [
		'cert',
		'email',
		'phpmailer',
	],
	'exception' => [
		'DbException',
	],
	'GarbageCollector' => [
		'GarbageCollector',
	],
	'html2pdf' => [
		'mpdf.php'
	],
	'menu' => [
		'menu',
	],
	'remote' => [
		'RemoteAccess',
	],
	'system' => [
		'apihandler',
		'cronhandler',
		'DateFunctions',
		'DB',
		'functions',
		'requesthandler',
		'settings',
		'Debugger',
		'Encoding',
		'resthandler',
		'PAGE',
		'FILE',
		'FACTORY'

	],
	'tpl' => [
		'TemplateParser',
		'tpl'
	],
	'uploadImage' => [
		'UploadImage',
	],
	'nextcloud' => [
		'NextCloudApi'
	],
	'webdav' => [
		'WebDav'
	],
	'data/klassentagebuch' => [
		'TagebuchTeacherEntry',
		'TagebuchKlasseEntry',
	    'TagebuchPDFCreator'
	],
	'print' => [
		'PrintNormalPageA4WithHeader',
		'PrintLetterWithWindowA4',
	    'PrintNormalPageA4WithoutHeader',
	    'PrintInBrowser'
	],
	'update' => [
		'UpdateProcess'
	],
	'messages' => [
		'Message',
		'MessageFolder',
		'RecipientHandler',
		'MessageSender',
		'MessageAttachment',
	    'MessageQuestion',
	    'MessageAnswer'
			
	],
	'messages/recipients' => [
		'AllTeacherRecipient',
		'MessageRecipient',
		'PupilsOfGrade',
		'TeacherRecipient',
		'UnknownRecipient',
		'UserRecipient',
		'FachschaftRecipient',
		'KlassenteamRecipient',
		'PupilRecipient',
		'ParentsOfGrade',
		'ParentRecipient',
	    'SchulleitungRecipient',
	    'HausmeisterRecipient',
	    'VerwaltungRecipient',
	    'PersonalratRecipient',
	    'GroupRecipient',
	    'KlassenleitungRecipient',
	    'PupilsOfClassRecipient',
	    'ParentsOfPupilsOfClassRecipient'
	],
	'data/schuelerinfo' => [
		'SchuelerDokument',
		'SchuelerNachteilsausgleich',
		'SchuelerUnterricht',
		'SchuelerBrief'
	],
	'uploads' => [
		'FileUpload'
	],
	'noten' => [
		'Note',
		'NoteArbeit',
	    'NoteZeugnisKlasse',
	    'NoteZeugnis',
	    'Notenbogen',
	    'UnterrichtsNoten',
	    'NotenCalculcator',
	    'NoteZeugnisNote',
	    'MV',
	    'MVFach',
	    'NoteWahlfach',
	    'NoteWahlfachNote',
	    'NoteVerrechnung',
	    'NoteZeugnisBemerkung',
	    'NoteGewichtung',
        'NotenFremdsprachenNiveaustufen'
	],
    'noten/bemerkung' => [
        'NoteBemerkungGruppe',
        'NoteBemerkungText'
    ]
];


function schuleinternautoloader($class) {
	global $classes;
	
	include_once("../framework/lib/system/requesthandler.class.php");
	include_once('../framework/lib/phpexcel/PHPExcel.php');
	
	if(PHPExcel_Autoloader::load($class)) return true;
	
	
	
	// Message Bird Lib?
	
	// project-specific namespace prefix
	$prefix = 'MessageBird\\';
	
	// base directory for the namespace prefix
	$base_dir = '../framework/lib/sms/messagebirdlib/src/MessageBird/';
	
	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
	    
	}
	
	// get the relative class name
	$relative_class = substr($class, $len);
	
	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
	
	// if the file exists, require it
	if (file_exists($file)) {
	    require $file;
	    return;
	}
	
	
	
	// if(in_array($class, requesthandler::getAllowedActions())) return;		// Seiten nicht automatisch laden, macht der Requesthandler
	
	
	if($class == "mPDF") {
		include_once '../framework/lib/html2pdf/mpdf.php';
		return;
	}
	if($class == 'TCPDF') {
		include_once '../framework/lib/tcpdf/tcpdf.php';
		return;
	}
	
	if($class == 'ZCiCal') {
		include_once ('../framework/lib/ical/zapcallib.php');
		return;
	}
	
	
	if(strtolower($class) == "phpmailer") {
		include_once '../framework/lib/email/phpmailer/class.phpmailer.php';
		include_once '../framework/lib/email/phpmailer/class.smtp.php';
		return;
	}
	
	if($class == "AbstractPage") return;
	
	if(requesthandler::loadPage($class)) return;
	
	foreach($classes as $c => $d) {
		for($i = 0; $i < sizeof($d); $i++) {
			if(strtolower($d[$i]) == strtolower($class)) {
			    // echo($class . "<br>");
			    
				include_once('../framework/lib/' . $c . '/' . $d[$i] . ".class.php");
				return;
			}
		}
	}
	
	// Unbekannte Klasse
	
	Debugger::debugObject("<h1>" . $class . " not found</h1>", true);
}



// Composer Autoloader laden
include_once("../framework/lib/composer/vendor/autoload.php");


