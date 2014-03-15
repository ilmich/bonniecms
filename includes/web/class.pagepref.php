<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	// Stores session variables unique to a given URL
	class PagePref {
		
		public $_id;
		public $_data;
	
		public function __construct() {
			$this->_id = 'pp' . md5($_SERVER['PHP_SELF']);
	
			if(!is_null(Session::getInstance()->getValue($this->_id)))
				$this->_data = unserialize(Session::getInstance()->getValue($this->_id));
		}
	
		public function __get($key) {
			return $this->_data[$key];
		}
	
		public function __set($key, $val) {
			if(!is_array($this->_data)) 
				$this->_data = array();
				
			$this->_data[$key] = $val;
			Session::getInstance()->setValue($this->_id,serialize($this->_data));
		}
	
		public function clear() {
			Session::getInstance()->unsetValue($this->_id);		
			unset($this->_data);
		}
	}
