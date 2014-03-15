<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	class Cache {
		
		public static function factory($params=array()) {			
			if (is_null($params) || !is_array($params) || empty($params)) {
				throw new ClydePhpException('You must pass a valid array of parameters');
			}
			
			if (!isset($params['type'])) {
				throw new ClydePhpException('You must pass a non empty type');
			}
			
			$adapterName = ucfirst($params['type'].'Cache');
	
			return new $adapterName($params);				
		}		
	}
	
	class CacheObject {
		
		public $value= null;
		public $expirationTime = null;
		
		public function __construct($value,$exp) {
			$this->value=$value;
			$this->expirationTime = time()+$exp;
		}
		
		public function isValid() {
			return time() < $this->expirationTime;
		}
		
		public function getValue() {
			return $this->value;
		}
		
		public function setValue($value) {
			$this->value = $value;
		}		
	}
	