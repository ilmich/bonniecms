<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	class HTML {
		
		public static function meta($name,$content) {			
			if (strtolower($name) === 'content-type') {
				return '<meta http-equiv=\'Content-Type\' content=\''.$content.'\' />';
			}
			
			return '<meta name=\''.$name.'\' content=\''.$content.'\'/>';
		}
		
		public static function anchor($url='#',$text=null,$extra='') {			
			return '<a href=\''.Url::htmlEncode($url).'\' '.$extra.'>'.$text.'</a>'; 			
		}
		
		public static function image($src='#',$extra='') {
			return '<img src=\''.Url::htmlEncode($src).'\' '.$extra.'/>';
		}		
	}