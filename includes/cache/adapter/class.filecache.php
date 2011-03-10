<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php
	
	class FileCache extends BaseClass {
		
		private $_expiration;
		private $_path;
		
		public function __construct($params = array()) {
			
			parent::__construct($params);
			if (is_null($this->getProperty('path'))) 
				throw new ClydePhpException("Path not configured for cache file store");
				
			if (is_null($this->getProperty('expiration'))) 
				throw new ClydePhpException("Expiration not configured for cache");
				
			$this->_path = $this->getProperty('path');
			$this->_expiration = $this->getProperty('expiration');			
			
		}
		
		public function put($key,$value,$tag=null) {
			
			$path = String::slash($this->_path);
			
			if (!is_null($tag)) {
				$path .= $tag.'/'; 	
			}
			
			if (!file_exists($path)) {
				@mkdir ($path,0777);				
			}
			
			if (!is_writable($this->_path))
				return false;
			
			$filename = $path.md5($key);
			file_put_contents($filename,serialize(new CacheObject($value,$this->_expiration)));
			
		}	

		public function get($key,$tag=null) {
			
			$path = String::slash($this->_path);
			
			if (!is_null($tag)) {
				$path .= $tag.'/'; 	
			}
			
			if (!is_readable($path)) 
				return null;
				
			$file = $path.md5($key);
			$obj = unserialize(@file_get_contents($file));
			
			if ($obj && $obj->isValid())
				return $obj->getValue();
		
			return null;
			
		}
	}