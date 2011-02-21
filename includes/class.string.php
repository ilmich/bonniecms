<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php

	class String {
		
		// Given a string such as "comment_123" or "id_57", it returns the final, numeric id.
		public static function splitId($str)
		{
			return self::match('/[_-]([0-9]+)$/', $str, 1);
		}
	
		// Creates a friendly URL slug from a string
		public static function slugify($str)
		{
			$str = preg_replace('/[^a-zA-Z0-9 -]/', '', $str);
			$str = strtolower(str_replace(' ', '-', trim($str)));
			$str = preg_replace('/-+/', '-', $str);
			return $str;
		}
		
		// Ensures $str ends with a single /
		public static function slash($str)
		{
			return rtrim($str, '/') . '/';
		}
		
		// Ensures $str DOES NOT end with a /
		public static function unslash($str)
		{
			return rtrim($str, '/');
		}		
		
		// Returns the first $num words of $str
		public static function maxWords($str, $num, $suffix = '')
		{
			$words = explode(' ', $str);
			if(count($words) < $num)
				return $str;
			else
				return implode(' ', array_slice($words, 0, $num)) . $suffix;
		}
		
		// Quick wrapper for preg_match
		public static function match($regex, $str, $i = 0)
		{
			if(preg_match($regex, $str, $match) == 1)
				return $match[$i];
			else
			return false;
		}
		
		// Fixes MAGIC_QUOTES
		public static function fixSlashes($arr = '')
		{
			if(is_null($arr) || $arr == '') 
				return null;
			if(!get_magic_quotes_gpc()) 
				return $arr;
		
			return is_array($arr) ? array_map(array('String','fixSlashes'), $arr) : stripslashes($arr);
		}
		
		// Formats a phone number as (xxx) xxx-xxxx or xxx-xxxx depending on the length.
		public static function format_phone($phone)
		{
			$phone = preg_replace("/[^0-9]/", '', $phone);
		
			if(strlen($phone) == 7)
			return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
			elseif(strlen($phone) == 10)
			return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
			else
			return $phone;
		}
		
	}

?>