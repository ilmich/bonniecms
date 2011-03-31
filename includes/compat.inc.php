<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php

// code from http://freshmeat.net/p/upgradephp

/**
 * Converts PHP variable or array into a "JSON" (JavaScript value expression
 * or "object notation") string.
 *
 * @compat
 *    Output seems identical to PECL versions. "Only" 20x slower than PECL version.
 * @bugs
 *    Doesn't take care with unicode too much - leaves UTF-8 sequences alone.
 *
 * @param  $var mixed  PHP variable/array/object
 * @return string      transformed into JSON equivalent
 */
if (!function_exists("json_encode")) {
	function json_encode($var, /*emu_args*/$obj=FALSE) {
		 
		#-- prepare JSON string
		$json = "";

		#-- add array entries
		if (is_array($var) || ($obj=is_object($var))) {

			#-- check if array is associative
			if (!$obj) foreach ((array)$var as $i=>$v) {
				if (!is_int($i)) {
					$obj = 1;
					break;
				}
			}

			#-- concat invidual entries
			foreach ((array)$var as $i=>$v) {
				$json .= ($json ? "," : "")    // comma separators
				. ($obj ? ("\"$i\":") : "")   // assoc prefix
				. (json_encode($v));    // value
			}

			#-- enclose into braces or brackets
			$json = $obj ? "{".$json."}" : "[".$json."]";
		}

		#-- strings need some care
		elseif (is_string($var)) {
			if (!utf8_decode($var)) {
				$var = utf8_encode($var);
			}
			$var = str_replace(array("\"", "\\", "/", "\b", "\f", "\n", "\r", "\t"), array("\\\"", "\\\\", "\\/", "\\b", "\\f", "\\n", "\\r", "\\t"), $var);
			$json = '"' . $var . '"';
			//@COMPAT: for fully-fully-compliance   $var = preg_replace("/[\000-\037]/", "", $var);
		}

		#-- basic types
		elseif (is_bool($var)) {
			$json = $var ? "true" : "false";
		}
		elseif ($var === NULL) {
			$json = "null";
		}
		elseif (is_int($var) || is_float($var)) {
			$json = "$var";
		}

		#-- something went wrong
		else {
			trigger_error("json_encode: don't know what a '" .gettype($var). "' is.", E_USER_ERROR);
		}

		#-- done
		return($json);
	}
}



/**
 * Parses a JSON (JavaScript value expression) string into a PHP variable
 * (array or object).
 *
 * @compat
 *    Behaves similar to PECL version, but is less quiet on errors.
 *    Now even decodes unicode \uXXXX string escapes into UTF-8.
 *    "Only" 27 times slower than native function.
 * @bugs
 *    Might parse some misformed representations, when other implementations
 *    would scream error or explode.
 * @code
 *    This is state machine spaghetti code. Needs the extranous parameters to
 *    process subarrays, etc. When it recursively calls itself, $n is the
 *    current position, and $waitfor a string with possible end-tokens.
 *
 * @param   $json string   JSON encoded values
 * @param   $assoc bool    (optional) if outer shell should be decoded as object always
 * @return  mixed          parsed into PHP variable/array/object
 */
if (!function_exists("json_decode")) {
	function json_decode($json, $assoc=FALSE, /*emu_args*/$n=0,$state=0,$waitfor=0) {

		#-- result var
		$val = NULL;
		static $lang_eq = array("true" => TRUE, "false" => FALSE, "null" => NULL);
		static $str_eq = array("n"=>"\012", "r"=>"\015", "\\"=>"\\", '"'=>'"', "f"=>"\f", "b"=>"\b", "t"=>"\t", "/"=>"/");

		#-- flat char-wise parsing
		for (/*n*/; $n<strlen($json); /*n*/) {
			$c = $json[$n];

			#-= in-string
			if ($state==='"') {

				if ($c == '\\') {
					$c = $json[++$n];
					// simple C escapes
					if (isset($str_eq[$c])) {
						$val .= $str_eq[$c];
					}

					// here we transform \uXXXX Unicode (always 4 nibbles) references to UTF-8
					elseif ($c == "u") {
						// read just 16bit (therefore value can't be negative)
						$hex = hexdec( substr($json, $n+1, 4) );
						$n += 4;
						// Unicode ranges
						if ($hex < 0x80) {    // plain ASCII character
							$val .= chr($hex);
						}
						elseif ($hex < 0x800) {   // 110xxxxx 10xxxxxx
							$val .= chr(0xC0 + $hex>>6) . chr(0x80 + $hex&63);
						}
						elseif ($hex <= 0xFFFF) { // 1110xxxx 10xxxxxx 10xxxxxx
							$val .= chr(0xE0 + $hex>>12) . chr(0x80 + ($hex>>6)&63) . chr(0x80 + $hex&63);
						}
						// other ranges, like 0x1FFFFF=0xF0, 0x3FFFFFF=0xF8 and 0x7FFFFFFF=0xFC do not apply
					}

					// no escape, just a redundant backslash
					//@COMPAT: we could throw an exception here
					else {
						$val .= "\\" . $c;
					}
				}

				// end of string
				elseif ($c == '"') {
					$state = 0;
				}

				// yeeha! a single character found!!!!1!
				else/*if (ord($c) >= 32)*/ { //@COMPAT: specialchars check - but native json doesn't do it?
					$val .= $c;
				}
			}

			#-> end of sub-call (array/object)
			elseif ($waitfor && (strpos($waitfor, $c) !== false)) {
				return array($val, $n);  // return current value and state
			}
			 
			#-= in-array
			elseif ($state===']') {
				list($v, $n) = json_decode($json, 0, $n, 0, ",]");
				$val[] = $v;
				if ($json[$n] == "]") { return array($val, $n); }
			}

			#-= in-object
			elseif ($state==='}') {
				list($i, $n) = json_decode($json, 0, $n, 0, ":");   // this allowed non-string indicies
				list($v, $n) = json_decode($json, 0, $n+1, 0, ",}");
				$val[$i] = $v;
				if ($json[$n] == "}") { return array($val, $n); }
			}

			#-- looking for next item (0)
			else {
				 
				#-> whitespace
				if (preg_match("/\s/", $c)) {
					// skip
				}

				#-> string begin
				elseif ($c == '"') {
					$state = '"';
				}

				#-> object
				elseif ($c == "{") {
					list($val, $n) = json_decode($json, $assoc, $n+1, '}', "}");
					if ($val && $n && !$assoc) {
						$obj = new stdClass();
						foreach ($val as $i=>$v) {
							$obj->{$i} = $v;
						}
						$val = $obj;
						unset($obj);
					}
				}
				#-> array
				elseif ($c == "[") {
					list($val, $n) = json_decode($json, $assoc, $n+1, ']', "]");
				}

				#-> comment
				elseif (($c == "/") && ($json[$n+1]=="*")) {
					// just find end, skip over
					($n = strpos($json, "*/", $n+1)) or ($n = strlen($json));
				}

				#-> numbers
				elseif (preg_match("#^(-?\d+(?:\.\d+)?)(?:[eE]([-+]?\d+))?#", substr($json, $n), $uu)) {
					$val = $uu[1];
					$n += strlen($uu[0]) - 1;
					if (strpos($val, ".")) {  // float
						$val = (float)$val;
					}
					elseif ($val[0] == "0") {  // oct
						$val = octdec($val);
					}
					else {
						$val = (int)$val;
					}
					// exponent?
					if (isset($uu[2])) {
						$val *= pow(10, (int)$uu[2]);
					}
				}

				#-> boolean or null
				elseif (preg_match("#^(true|false|null)\b#", substr($json, $n), $uu)) {
					$val = $lang_eq[$uu[1]];
					$n += strlen($uu[1]) - 1;
				}

				#-- parsing error
				else {
					// PHPs native json_decode() breaks here usually and QUIETLY
					trigger_error("json_decode: error parsing '$c' at position $n", E_USER_WARNING);
					return $waitfor ? array(NULL, 1<<30) : NULL;
				}

			}//state
			 
			#-- next char
			if ($n === NULL) { return NULL; }
			$n++;
		}//for

		#-- final result
		return ($val);
	}
}

//imageconvolution clone with some lite modification
//WARNING: this function work only with truecolor image
//code from http://mgccl.com/2007/03/02/simple-replication-of-imageconvolution-function
//
//include this file whenever you have to use imageconvolution...
//you can use in your project, but keep the comment below :)
//great for any image manipulation library
//Made by Chao Xu(Mgccl) 2/28/07
//www.webdevlogs.com
//V 1.0
if(!function_exists('imageconvolution')){
	function imageconvolution($src, $filter, $filter_div, $offset){
		if ($src==NULL) {
			return false;
		}
		
		if ($filter_div == 0) $filter_div = 1;
		
		$pxl=array(0,0);
		$sx = imagesx($src);
		$sy = imagesy($src);				
		$srcback = imagecreatetrueColor($sx, $sy);		
		imagecopy($srcback, $src,0,0,0,0,$sx,$sy);
	 
		if($srcback==NULL){
			return 0;
		}
	
		for ($y=0; $y<$sy; $y++){
			for($x=0; $x<$sx; $x++){
				$new_r = $new_g = $new_b = 0;
				$alpha = imagecolorat($srcback, $pxl[0], $pxl[1]);
				$new_a = ($alpha >> 24) & 0x7F000000;
				
				for ($j=0; $j<3; $j++) {
					$yv = min(max($y - 1 + $j, 0), $sy - 1);
					for ($i=0; $i<3; $i++) {
					    $pxl = array(min(max($x - 1 + $i, 0), $sx - 1), $yv);
					    $rgb = imagecolorat($srcback, $pxl[0], $pxl[1]);
						$new_r += (($rgb >> 16) & 0xFF) * $filter[$j][$i];
						$new_g += (($rgb >> 8) & 0xFF) * $filter[$j][$i];
						$new_b += ($rgb & 0xFF) * $filter[$j][$i];						
					}
				}
	 
				$new_r = ($new_r/$filter_div)+$offset;
				$new_g = ($new_g/$filter_div)+$offset;
				$new_b = ($new_b/$filter_div)+$offset;
	 
				$new_r = ($new_r > 255)? 255 : (($new_r < 0)? 0:$new_r);
				$new_g = ($new_g > 255)? 255 : (($new_g < 0)? 0:$new_g);
				$new_b = ($new_b > 255)? 255 : (($new_b < 0)? 0:$new_b);
	 				
				$new_pxl = imagecolorallocatealpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
				if ($new_pxl == -1) {
					$new_pxl = imagecolorclosestalpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
				}
				
				if (($y >= 0) && ($y < $sy)) {
					imagesetpixel($src, $x, $y, $new_pxl);
				}
			}
		}
		imagedestroy($srcback);
		return true;
	}
}

?>