<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }
	
	class FileCache extends BaseClass {
		
		private $_expiration;
		private $_path;
		
		public function __construct($params = array()) {			
			parent::__construct($params);
			
			if (is_null($this->getProperty('path'))) 
				throw new ClydePhpException('Path not configured for cache file store');
				
			if (is_null($this->getProperty('expiration'))) 
				throw new ClydePhpException('Expiration not configured for cache');
				
			$this->_path = String::slash($this->getProperty('path'));
			$this->_expiration = $this->getProperty('expiration');			
		}
		
		public function put($key,$value,$tag=null) {
			$path=$this->_path;			
			if (!is_null($tag)) {
				$path .= $tag.'/'; 	
			}
			
			if (!file_exists($path)) {
				@mkdir ($path,0777);				
			}
			
			if (!is_writable($this->_path))
				return false;
			
			$filename = $path.md5($key);
			return file_put_contents($filename,serialize(new CacheObject($value,$this->_expiration)));			
		}	

		public function get($key,$tag=null) {
			$path=$this->_path;			
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
		
		public function delete($key,$tag=null) {
			$path=$this->_path;
			if (!is_null($tag)) {
				$path .= $tag.'/'; 	
			}			
			
			if (!is_writable($this->_path))
				return false;
				
			return @unlink($path.md5($key));
		}
		
		public function clear($tag=null) {
			$path=$this->_path;			
			if (!is_null($tag)) {
				$path .= $tag.'/'; 	
			}	
			
			if (!is_writable($this->_path))
				return false;
				
			$keys = glob($path.'*');
			array_map('unlink',$keys);
			
			return @rmdir($path);
		}
	}