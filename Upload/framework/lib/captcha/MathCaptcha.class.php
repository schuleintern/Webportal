<?php 

class MathCaptcha {
	
	public static function showCaptcha() {
		$captcha = DB::getDB()->query_first("SELECT * FROM math_captcha WHERE captchaID='" . DB::getDB()->escapeString($_GET['captchaID']) . "'");
		
		if($captcha['captchaID'] > 0 && $captcha['captchaSecret'] == $_GET['a']) {
			// Captcha anzeigen
				
			header ("Content-type: image/png");
			$im = @ImageCreate (100, 30)
			or die ("Kann keinen neuen GD-Bild-Stream erzeugen");
			$background_color = ImageColorAllocate ($im, 200, 200, 200);
			$text_color = ImageColorAllocate ($im, 233, 14, 91);
			ImageString ($im, 5, 5, 5, $captcha['captchaQuestion'], $text_color);
			ImagePNG ($im);
				
			exit(0);
		}
		else die("Unknown Captcha!");
	}
	
	public static function checkCaptcha($id, $secret, $solution) {
		$captcha = DB::getDB()->query_first("SELECT * FROM math_captcha WHERE captchaID='" . DB::getDB()->escapeString($id) . "'");
		
		// Nur einmal verwendbar:
		if($captcha['captchaID'] > 0) DB::getDB()->query("DELETE FROM math_captcha WHERE captchaID='" . DB::getDB()->escapeString($id) . "'");
		
		if($captcha['captchaID'] == $id && $captcha['captchaSecret'] == $secret && $captcha['captchaSolution'] == $solution) {
			return true;
		}
		
		return false;
	}
	
	public static function getCaptureHTMLCode() {
		$number1 = rand(1,20);
		$number2 = rand(1,20);
		
		$rechnung = rand(0,1);
		
		if($rechnung == 0) {
			// Plus
			$question = $number1 . " + " . $number2 . " = ";
			$solution = $number1 + $number2;
		}
		else {
			if($number2 > $number1) {
				$question = $number2 . " - " . $number1 . " = ";
				$solution = $number2 - $number1;
			}
			else {
				$question = $number1 . " - " . $number2 . " = ";
				$solution = $number1 - $number2;
			}
		}
		
		$secret = substr(md5(rand()), 0, 4);
		
		DB::getDB()->query("INSERT INTO math_captcha (captchaID, captchaQuestion, captchaSolution, captchaSecret) values(NULL, '" . $question . "','". $solution . "','" . $secret . "')");
		
		$newID = DB::getDB()->insert_id();
		
		return "<img src=\"index.php?page=GetMathCaptcha&captchaID=" . $newID . "&a=" . $secret . "\"><input type=\"hidden\" name=\"captchaID\" value=\"" . $newID . "\"><input type=\"hidden\" name=\"a\" value=\"" . $secret . "\"><input type=\"text\" name=\"captcha\" class=\"form-control\" placeholder=\"L&ouml;sung hier eintragen\">";
		
	}
	
}


?>