<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php

	class HTML {
		
		public static function meta($name,$content) {
			return "<meta name='$name' content='$content'/>";
		}
		
		public static function anchor($url="#",$text=null,$extra="") {
			return "<a href='$url' $extra>$text</a>"; 			
		}
		
		public static function image($src="#",$extra="") {
			return "<img src='$src' $extra/>";
		}
		
	}