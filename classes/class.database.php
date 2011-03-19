<?php if (!defined('CLYDEPHP')) die('Direct access not allowed') ;?>
<?php

	require_once CLYDEPHP_VENDOR.'pecora/pecora.php';
		
	class Database {
				
		private static $me=array();		
		
		public function getDatabase($name) {
			if(!isset(self::$me[$name])) {
				self::$me[$name] = new Pecora(getDataDir(),$name);
			}
			
			return self::$me[$name];
		}		
	}