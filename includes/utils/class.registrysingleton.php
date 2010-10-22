<?php

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
?>