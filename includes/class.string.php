<?php if (!defined('CLYDEPHP')) die('Direct access not allowed') ;?>
<?php

	class String {
		
		// Given a string such as "comment_123" or "id_57", it returns the final, numeric id.
		public static function splitId($str) {
			return self::match('/[_-]([0-9]+)$/', $str, 1);
		}
	
		// Creates a friendly URL slug from a string
		public static function slugify($str) {			
			$str = preg_replace('/[^a-zA-Z0-9 -]/', '', $str);
			$str = strtolower(str_replace(' ', '-', trim($str)));
			$str = preg_replace('/-+/', '-', $str);
			return $str;
		}
		
		// Ensures $str ends with a single /
		public static function slash($str) {
			return rtrim($str, '/') . '/';
		}
		
		// Ensures $str DOES NOT end with a /
		public static function unslash($str) {
			return rtrim($str, '/');
		}		
		
		// Returns the first $num words of $str
		public static function maxWords($str, $num, $suffix = '') {
			$words = explode(' ', $str);
			if(count($words) < $num)
				return $str;
			else
				return implode(' ', array_slice($words, 0, $num)) . $suffix;
		}
		
		// Quick wrapper for preg_match
		public static function match($regex, $str, $i = 0) {
			if(preg_match($regex, $str, $match) == 1)
				return $match[$i];
			else
				return false;
		}
		
		// Fixes MAGIC_QUOTES
		public static function fixSlashes($arr = '') {
			if(is_null($arr) || $arr == '') 
				return null;
			if(!get_magic_quotes_gpc()) 
				return $arr;
		
			return is_array($arr) ? array_map(array('String','fixSlashes'), $arr) : stripslashes($arr);
		}
		
		// Formats a phone number as (xxx) xxx-xxxx or xxx-xxxx depending on the length.
		public static function format_phone($phone) {
			$phone = preg_replace("/[^0-9]/", '', $phone);
		
			if(strlen($phone) == 7)
				return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
			elseif(strlen($phone) == 10)
				return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
			else
				return $phone;
		}
		
		/**
		 * Check if a string is encoded in utf-8
		 * 
		 * from http://it.php.net/manual/en/function.mb-check-encoding.php#95289
		 * 
		 * @param $str
		 */
		public static function checkUtf8($str) {
		    $len = strlen($str);
		    for($i = 0; $i < $len; $i++){
		        $c = ord($str[$i]);
		        if ($c > 128) {
		            if (($c > 247)) return false;
		            elseif ($c > 239) $bytes = 4;
		            elseif ($c > 223) $bytes = 3;
		            elseif ($c > 191) $bytes = 2;
		            else return false;
		            if (($i + $bytes) > $len) return false;
		            while ($bytes > 1) {
		                $i++;
		                $b = ord($str[$i]);
		                if ($b < 128 || $b > 191) return false;
		                $bytes--;
		            }
		        }
		    }
		    return true;
		}

		public static function isNullOrEmpty($str) {
			return is_null($str) || $str === '';
		}
		
		public static function nullToEmpty($str) {
			if (String::isNullOrEmpty($str)) {
				return '';
			}
			return $str;
		}
		
		public static function emptyToNull($str) {
			if (String::isNullOrEmpty($str)) {
				return null;
			}
			return $str;
		}
	}
