<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }
	
	class RegistrySingleton {
			
		// Singleton object. Leave $me alone.
		private static $singletons = array();
	
		public static function getSingleton($className) {			 
			if (!isset(self::$singletons[$className])) {
				self::$singletons[$className] = new $className();
			}			 
			return self::$singletons[$className];
		}	
	}
