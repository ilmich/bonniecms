<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php

	class Url {
		
		// Computes the *full* URL of the current page (protocol, server, path, query parameters, etc)
		public static function fullUrl()
		{
			$s = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
			$protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/')) . $s;
			$port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (":".$_SERVER['SERVER_PORT']);
			return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
		}
		
		// Processes mod_rewrite URLs into key => value pairs
		// See .htacess for more info.
		public static function pickOff($grab_first = false, $sep = '/')
		{
			$ret = array();
			$arr = explode($sep, trim($_SERVER['REQUEST_URI'], $sep));
			
			if($grab_first) 
				$ret[0] = array_shift($arr);
				
			while(count($arr) > 0)
				$ret[array_shift($arr)] = array_shift($arr);
				
			return (count($ret) > 0) ? $ret : false;
		}
					
		/**
		 * better encoding url
		 * 
		 * code snippet from
		 * http://publicmind.in/blog/url-encoding/
		 * 
		 * @param $url
		 */
		public static function encode($url) {
			
			$reserved = array(
					":" => '!%3A!ui',
					"/" => '!%2F!ui',
					"?" => '!%3F!ui',
					"#" => '!%23!ui',
					"[" => '!%5B!ui',
					"]" => '!%5D!ui',
					"@" => '!%40!ui',
					"!" => '!%21!ui',
					"$" => '!%24!ui',
					"&" => '!%26!ui',
					"'" => '!%27!ui',
					"(" => '!%28!ui',
					")" => '!%29!ui',
					"*" => '!%2A!ui',
					"+" => '!%2B!ui',
					"," => '!%2C!ui',
					";" => '!%3B!ui',
					"=" => '!%3D!ui',
					"%" => '!%25!ui',
				);
				
				$url = rawurlencode(utf8_encode($url));
				$url = preg_replace(array_values($reserved), array_keys($reserved), $url);
				
				return $url;
			}

			public static function htmlEncode($url) {
				return self::encode(htmlentities($url));
			}
				
	}