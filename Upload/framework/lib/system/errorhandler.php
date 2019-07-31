<?php

function schuleinternerrorhandler ($code, $msg, $file, $line, $context) {

    if(isFatalError($code) && DB::isDebug()) {
		// Fehler anzeigen
		?>
		<html>
			<head>
				<title>SchuleIntern Fatal Error</title>
			</head>
			<body>
				<center>
				<h1>FatalError Executing Schule Intern</h1>
			
				<table border="1" width="50%">
					<tr>
						<td width="15%">Code</td>
						<td><?php echo($code); ?></td>
					</tr>
					<tr>
						<td width="15%">Nachricht</td>
						<td><?php echo($msg); ?></td>
					</tr>
					<tr>
						<td width="15%">Datei</td>
						<td><?php echo($file); ?></td>
					</tr>
					<tr>
						<td width="15%">Zeile</td>
						<td><?php echo($line); ?></td>
					</tr>
					<tr>
						<td width="15%">Kontext</td>
						<td><pre><?php print_R($context); ?></pre></td>
					</tr>
				</table>
				</center>
			</body>
		
		</html>
		<?php
	}
	else if(isFatalError($code)) {
	    $text = "FatalError Executing Schule Intern
Code: $code
Nachricht: $msg
Datei: $file
Zeile: $line
Kontext: " . print_R($context, true);
	    
	    $fehlerID = reportError($text);
	    
	    if(substr($fehlerID,0,6) == 'ERROR_') $failed = true;
	    else $failed = false;
	    
	    ?>
	    
	    
<!DOCTYPE html>
<html>
  
  <head>
     <meta charset="UTF-8">
  
    <title>Schwerer Fehler</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="cssjs/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="cssjs/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="cssjs/font/ionicons/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="cssjs/dist/css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="cssjs/plugins/iCheck/square/blue.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="login-page">

    <div class="login-box">
      <div class="login-logo">
        <a href="index.php"><img src="imagesSchool/Icon.png" width="150" border="0"></a>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
      	<div class="callout callout-danger"><i class="fa fa-exclamation-triangle"></i> Fehler bei der Ausführung der Anfrage.</div>
        <p class="login-box-msg">Leider ist bei der Ausführung der Software ein schwerer Fehler aufgetreten. Die Entwickler wurden über diesen Fehler automatisch informiert. Bitte versuchen Sie es nocheinmal. Falls der Fehler weiterhin besteht, wenden Sie sich bitte an den Support unter <a href="https://support.schule-intern.de" target="_blank">https://support.schule-intern.de</a></p>
      	<code>Technische Informationen:
<?php echo DB::getGlobalSettings()->siteNamePlain; if(!$failed) echo ("-" . $fehlerID); ?></code>
      
      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->
    
    <script src="cssjs/plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <script src="cssjs/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="cssjs/plugins/iCheck/icheck.min.js" type="text/javascript"></script>
    
  </body>
</html>

<?php 
	  exit();  
	}
	else {
		// Kein fateler Fehler.
	}

}

function isFatalError($code) {
	return $code == E_ERROR || $code == E_PARSE || $code == E_COMPILE_ERROR || $code == E_CORE_ERROR;
}

function reportError($text) {
    #
    # Configuration: Enter the url and key. That is it.
    #  url => URL to api/task/cron e.g #  http://yourdomain.com/support/api/tickets.json
    #  key => API's Key (see admin panel on how to generate a key)
    #
    
    $config = array(
    'url'=>'https://support.schule-intern.de/api/tickets.json',
    'key'=>'8DCBB5245D4D8B92317E89337E0A02D7'
						    );
    
    # Fill in the data for the new ticket, this will likely come from $_POST.
    
    $data = array(
    'name'      =>      'SchuleIntern System',
    'email'     =>      'systemerror@schule-intern.de',
    'subject'   =>      'System Fehler in Installation ' . DB::getGlobalSettings()->siteNamePlain,
    'message'   =>      $text,
    'ip'        =>      '93.229.83.178',
    'attachments' => array(),
    );
    
    
    #pre-checks
    function_exists('curl_version') or die('CURL support required');
    function_exists('json_encode') or die('JSON support required');
    
    #set timeout
    set_time_limit(3);
    
    #curl post
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['url']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_USERAGENT, 'osTicket API Client v1.7');
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:', 'X-API-Key: '.$config['key']));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result=curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code != 201)
        return 'ERROR_'.$result;
        
        return (int) $result;
        
}