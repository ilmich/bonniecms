<?php if (!defined('CLYDEPHP')) die('Direct access not allowed') ;?>
<?php
	
	class Lang {
		
		private $locale = 'en';		
		private $messages = array();
		private static $me=null;
		
		private function __construct() {
			
		}
		
		private function getLang() {
			if(is_null(self::$me)) {
				self::$me = new Lang();
			}
			
			return self::$me;
		}
		
		public static function setLocale($locale) {
			self::getLang()->locale=$locale;		
		}
		
		public static function getLocale() {
			return self::getLang()->locale;			
		}
		
		public static function loadMessages($name,$basePath=null) {
			$instance = self::getLang();
			if (is_null($basePath)) {
				$basePath = APP_ROOT;
			}
			$filename = $basePath.'lang/'.$instance->locale.'/'.$name.'.php';
			if (!is_readable($filename)) {
				return false;
			}			
			$instance->messages = array_merge($instance->messages,require_once ($filename));	
		}
		
		public static function getMessage($key){
			$messages = self::getLang()->messages;
			if (!isset($messages[$key])) {
				return $key;		
			}
			return $messages[$key];
		}		
	}