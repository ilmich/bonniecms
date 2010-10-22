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
				
	}