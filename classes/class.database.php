<?php if (!defined('CLYDEPHP')) die('Direct access not allowed') ;?>
<?php

	require_once CLYDEPHP_VENDOR.'pecora/pecora.php';
		
	class Database {
				
		private static $me=array();
				
		/**
		 * Get a database
		 * 
		 * @param string $name name of the database
		 * @return Pecora 
		 */
		public function getDatabase($name,$absolute=false) {
			if(!isset(self::$me[$name])) {
				self::$me[$name] = new Pecora(getDataDir($absolute),$name);
			}
			
			return self::$me[$name];
		}		
	}