<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

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