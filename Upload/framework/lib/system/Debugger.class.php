<?php

/**
 * Einfache Debugger Klasse
 * @todo Sollte erweitert werden.
 */
class Debugger {
	public static function debugObject($o, $stop=false) {
		?>
		<html>
			<head></head>
			<body>
				<table border="1">
					<tr>
						<td><pre><?php debug_print_backtrace(); ?></pre></td>
					</tr>
					<tr>
						<td><pre><?php print_r($o); ?></pre></td>
					</tr>
				</table>
			</body>
		</html>	
		<?php 
		if($stop) exit(0);
	}
}

