<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	class ArrayUtils {

		/**
		 * Returns an array of the values of the specified column from a multi-dimensional array
		 * 
		 * @param array $arr
		 * @param mixed $key
		 * @return array 
		 */
		public static function gimme($arr, $key = null) {
			if(is_null($key))
				$key = current(array_keys($arr));
		
			$out = array();
			foreach($arr as $a)
				$out[] = $a[$key];
		
			return $out;
		}
		
		/**
		 * Transform an array into an xml
		 * 
		 * portion of good kirby library:)		 * 
		 * 
		 * @param $array the source array
		 * @param $tag the name of the root tag
		 * @param $head flag that enable xml header
		 * @param $charset the xml charset
		 * @param $tab define the tabulation characters
		 * @param $level the start level		 * 
		 * @return string the resulting xml
		 */
		public static function toXml($array, $tag='root', $head=true, $charset='utf-8', $tab='  ', $level=0) {
			$result  = ($level==0 && $head) ? '<?xml version="1.0" encoding="' . $charset . '"?>' . "\n" : '';
			$nlevel  = ($level+1);
			$result .= str_repeat($tab, $level) . '<' . $tag . '>' . "\n";
			foreach($array AS $key => $value) {
				$key = strtolower($key);
				if(is_array($value)) {
					$mtags = false;
					foreach($value AS $key2 => $value2) {
						if(is_array($value2)) {
							$result .= self::toXml($value2, $key, $head, $charset, $tab, $nlevel);
						} else if(trim($value2) != '') {
							$value2  = (htmlspecialchars($value2) != $value2) ? '<![CDATA[' . $value2 . ']]>' : $value2;
							$result .= str_repeat($tab, $nlevel) . '<' . $key . '>' . $value2 . '</' . $key . '>' . "\n";
						}
						$mtags = true;
					}
					if(!$mtags && count($value) > 0) {
						$result .= self::toXml($value, $key, $head, $charset, $tab, $nlevel);
					}
				} else if(trim($value) != '') {
					$value   = (htmlspecialchars($value) != $value) ? '<![CDATA[' . $value . ']]>' : $value;
					$result .= str_repeat($tab, $nlevel) . '<' . $key . '>' . $value . '</' . $key . '>' . "\n";
				}
			}
			return $result . str_repeat($tab, $level) . '</' . $tag . '>' . "\n";
		}

		public static function exportIntoFile($array,$name,$filename,$header=null) {			
			if (!is_null($header)) {
				$str = $header.PHP_EOL;
			}else {
				$str = '';
			}
			$str.= '<?php '.PHP_EOL;
			$str.= '$'.$name.' = ';
			$str.= var_export($array,true).';'.PHP_EOL;
			$str.= 'return $'.$name.';'.PHP_EOL;
			
			return @file_put_contents($filename,$str);				
		}
	}
	