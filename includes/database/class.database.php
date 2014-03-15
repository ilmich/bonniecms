<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	abstract class Database  {
		
		const TYPE_NOSQL = "NoSql";
		const TYPE_SQL = "Sql";
		const ROOT_CONFIG_NODE = 'database';
    
		// Singleton object. Leave $me alone.
        private static $singletons = array();
        
        // Get Singleton object
        protected static function getSingleton($name,$params=array()) {        	
        	if (!isset(self::$singletons[$name])) {
        		self::$singletons[$name] = self::factory($params);
        	}        	
        	return self::$singletons[$name];
        }

        // Waiting (not so) patiently for 5.3.0...
        public static function __callStatic($name, $args) {
            return self::$me->__call($name, $args);
        }

        public static function getDatabase($name="default", $connect = true) {
        	$conf = Config::get(self::ROOT_CONFIG_NODE);
        				
			if (is_null($conf)) {
				throw new DatabaseException("Config for database ".$name." not found");
			}			
			if (!isset($conf[$name]['type']) || $conf[$name]['type'] === '') {
				throw new DatabaseException("No database type provided for database ".$name);
			}        	
			$conf[$name]['_connect'] = $connect;        	
            return self::getSingleton($name,$conf[$name]);
        }
        
        public static function factory($params) {
        	if (!isset($params['type']) || $params['type'] === '') {
				throw new DatabaseException("No database type provided");
			}        	
			$adapterName = ucfirst($params['type']."Adapter");			
			return new $adapterName($params);        	
        }
        
	}
