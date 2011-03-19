<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php
	
	class ApcCache extends BaseClass {
		
		private $_expiration;
		private $_path;
		
		public function __construct($params = array()) {
			if (!function_exists('apc_fetch')) 
				throw new ClydePhpException('Apc extension is not avaible');
				
			if (!ini_get('apc.enabled'))
				trigger_error('Apc extension is not enabled',E_USER_WARNING);
			
			parent::__construct($params);
			if (is_null($this->getProperty('expiration'))) 
				throw new ClydePhpException('Expiration not configured for cache');

			$this->_expiration = $this->getProperty('expiration');			
		}
		
		public function put($key,$value,$tag=null) {
			return apc_store($key,serialize($value),$this->_expiration);						
		}	

		public function get($key,$tag=null) {
			$obj = apc_fetch($key);	
			if ($obj)		
				return unserialize($obj);
			return null;
		}
		
		public function delete($key,$tag=null) {
			return apc_delete($key);
		}
		
		public function clear($tag) {
			return apc_clear_cache();	
		}
	}