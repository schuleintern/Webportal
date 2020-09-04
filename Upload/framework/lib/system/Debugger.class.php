<?php

/**
 * Einfache Debugger Klasse
 * 
 * 
 */

class Debugger {

	public static $dump = [];
	public static $queries = [];

	public static function getDevTools() {

		$html = '';
		$html .= self::getDbQueries();
		$html .= self::getDump();
		return $html;
	}

	public static function getDbQueries() {

		
		// echo "<pre>";
		// print_r(self::$queries);
		// echo "</pre>";

		$html = '<div class="queries">';
		if (count(self::$queries) > 0) {
			$html .= '<div class="devIcon">'.count(self::$queries).'</div>';
		}
		$html .= '<div class="devContent">';
		$html .= '<div class="devContentClose"></div>';
		for($i = 0; $i < count(self::$queries); $i++ ) {
			$html .= '<div class="item">';
			$html .= self::$queries[$i]['query'];
			if (self::$queries[$i]['error']) {
				$html .= '<div class="error">'.self::$queries[$i]['query'].'</div>';
			}
			if (self::$queries[$i]['info']['file']) {
				$html .= '<div class="item-header">'.self::$queries[$i]['info']['file'].' (Line: '.self::$queries[$i]['info']['line'].')</div>';
			}
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	public static function getDump() {
		
		// echo '<pre>';
		// print_r( self::$dump );
		// echo '</pre>';

		$html = '<div class="dump">';
		if (count(self::$dump) > 0) {
			$html .= '<div class="devIcon">'.count(self::$dump).'</div>';
		}
		$html .= '<div class="devContent">';
		$html .= '<div class="devContentClose"></div>';
		for($i = 0; $i < count(self::$dump); $i++ ) {
			$html .= '<div class="item">';
			$html .= '<div class="item-header">';
			$html .= self::$dump[$i][1]['file'].' (Line: '.self::$dump[$i][1]['line'].')';
			$html .= '</div>';
			$html .= '<pre>'.self::$dump[$i][0].'</pre>';
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	public static function debugQuery($query) {
		if ($query['query']) {
			$query['info'] = debug_backtrace()[1];
			array_push( self::$queries, $query );
		}
	}

	public static function debugObject($o, $stop=false) {
		
		if ( is_array($o) ) {
			if ( count($o) > 1 ) {
				$o = json_encode($o);
			} else {
				if ( is_array($o[0]) ) {
					$o = json_encode($o[0]);
				} else {
					$o = $o[0];
				}
			}
		}

		

		$info = debug_backtrace()[1];

		 echo '<pre>';
		 print_r( $o );
		 echo '</pre>';

		 echo '<pre>';
		 print_r( $info );
		 echo '</pre>';

		array_push(self::$dump, [$o, ['file' => $info['file'], 'line' => $info['line']]] );


		if($stop) exit(0);

		/*
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
		*/
		
	}
}

