<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	function printr($var) {		
		$output = formatText2Html(print_r($var, true));
		echo '<div style=\'font-family:courier;\'>'.$output.'</div>';		
	}
	
	function formatText2Html($text) {
		$text = str_replace("\n", '<br>', $text);
		$text = str_replace(' ', '&nbsp;', $text);
		return $text;
	}
	
	// Formats a given number of seconds into proper mm:ss format
	function format_time($seconds) {
		return floor($seconds / 60) . ':' . str_pad($seconds % 60, 2, '0');
	}
	
	// Serves an external document for download as an HTTP attachment.
	function download_document($filename, $mimetype = 'application/octet-stream') {
		if(!file_exists($filename) || !is_readable($filename)) return false;
		$base = basename($filename);
	
		$resp = new HttpResponse();
		$resp->addHeader('Cache-Control','must-revalidate, post-check=0, pre-check=0')
			->addHeader('Content-Disposition','attachment; filename=$base')
			->addHeader('Content-Length', filesize($filename))
			->addHeader('Content-Type',$mimetype)
			->setBody(file_get_contents($filename))
			->send();
		
		exit();
	}
	
	// Retrieves the filesize of a remote file.
	function remote_filesize($url, $user = null, $pw = null) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
		if(!is_null($user) && !is_null($pw)) {
			$headers = array('Authorization: Basic ' .  base64_encode("$user:$pw"));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
	
		$head = curl_exec($ch);
		curl_close($ch);
	
		preg_match('/Content-Length:\s([0-9].+?)\s/', $head, $matches);
	
		return isset($matches[1]) ? $matches[1] : false;
	}
	
	// Outputs a filesize in human readable format.
	function bytes2str($val, $round = 0) {
		$unit = array('','K','M','G','T','P','E','Z','Y');
		while($val >= 1000)	{
			$val /= 1024;
			array_shift($unit);
		}
		return round($val, $round) . array_shift($unit) . 'B';
	}
	
	// Tests for a valid email address and optionally tests for valid MX records, too.
	function valid_email($email, $test_mx = false) {
		if(preg_match("/^([_a-z0-9+-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email))	{
			if($test_mx) {
				list( , $domain) = explode("@", $email);
				return getmxrr($domain, $mxrecords);
			}
			else
				return true;
		}
		else
			return false;
	}
	
	// Grabs the contents of a remote URL. Can perform basic authentication if un/pw are provided.
	function geturl($url, $username = null, $password = null) {
		if(function_exists('curl_init')) {
			$ch = curl_init();
			
			if(!is_null($username) && !is_null($password))
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' .  base64_encode("$username:$password")));
				
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$html = curl_exec($ch);
			curl_close($ch);
			
			return $html;
			
		}
		elseif(ini_get('allow_url_fopen') == true) {
			if(!is_null($username) && !is_null($password))
			$url = str_replace("://", "://$username:$password@", $url);
			$html = file_get_contents($url);
			
			return $html;
		}
		else {
			// Cannot open url. Either install curl-php or set allow_url_fopen = true in php.ini
			return false;
		}
	}
	
	// Returns the user's browser info.
	// browscap.ini must be available for this to work.
	// See the PHP manual for more details.
	function browser_info() {
		$info    = get_browser(null, true);
		$browser = $info['browser'] . ' ' . $info['version'];
		$os      = $info['platform'];
		$ip      = $_SERVER['REMOTE_ADDR'];
		
		return array('ip' => $ip, 'browser' => $browser, 'os' => $os);
	}
	
	// Sends an HTML formatted email
	function send_html_mail($to, $subject, $msg, $from, $plaintext = '') {
		if(!is_array($to)) $to = array($to);
	
		foreach($to as $address) {
			$boundary = uniqid(rand(), true);
	
			$headers  = "From: $from\n";
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: multipart/alternative; boundary = $boundary\n";
			$headers .= "This is a MIME encoded message.\n\n";
			$headers .= "--$boundary\n" .
	                        "Content-Type: text/plain; charset=ISO-8859-1\n" .
	                        "Content-Transfer-Encoding: base64\n\n";
			$headers .= chunk_split(base64_encode($plaintext));
			$headers .= "--$boundary\n" .
	                        "Content-Type: text/html; charset=ISO-8859-1\n" .
	                        "Content-Transfer-Encoding: base64\n\n";
			$headers .= chunk_split(base64_encode($msg));
			$headers .= "--$boundary--\n" .
	
			mail($address, $subject, '', $headers);
		}
	}
	
	// Returns the lat, long of an address via Yahoo!'s geocoding service.
	// You'll need an App ID, which is available from here:
	// http://developer.yahoo.com/maps/rest/V1/geocode.html
	function geocode($location, $appid) {
		$location = urlencode($location);
		$appid    = urlencode($appid);
		$data     = file_get_contents("http://local.yahooapis.com/MapsService/V1/geocode?output=php&appid=$appid&location=$location");
		$data     = unserialize($data);
	
		if($data === false) 
			return false;
	
		$data = $data['ResultSet']['Result'];
	
		return array('lat' => $data['Latitude'], 'lng' => $data['Longitude']);
	}
	
	// Quick and dirty wrapper for curl scraping.
	function curl($url, $referer = null, $post = null) {
		static $tmpfile;
	
		if(!isset($tmpfile) || ($tmpfile == '')) 
			$tmpfile = tempnam('/tmp', 'FOO');
	
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfile);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfile);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1) Gecko/20061024 BonEcho/2.0");
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		if($referer) 
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		if(!is_null($post))	{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
	
		$html = curl_exec($ch);
	
		// $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		return $html;
	}
	
	// Accepts any number of arguments and returns the first non-empty one
	function pick() {
		foreach(func_get_args() as $arg)
			if(!empty($arg))
				return $arg;
		
		return '';
	}
	
	// Secure a PHP script using basic HTTP authentication
	function http_auth($un, $pw, $realm = "Secured Area") {
		if(!(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_USER'] == $un && $_SERVER['PHP_AUTH_PW'] == $pw)) {
			header('WWW-Authenticate: Basic realm="' . $realm . '"');
			header('Status: 401 Unauthorized');
			
			exit();
		}
	}
