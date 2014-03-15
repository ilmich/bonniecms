<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	class ClassLoader {
		
		// Singleton object. Leave $me alone.
		private static $me;
	
		private $classpath = array();
	
		/**
		 * Standard singleton
		 * @return ClassLoader
		 */
		public static function getInstance() {
			if(is_null(self::$me))
				self::$me = new ClassLoader();
			return self::$me;
		}
	
		private function __construct() {
			 
		}
	
		public function addClassPath($path) {	 
			$path = String::slash($path);
			
			if (!file_exists($path) || !is_dir($path)) {
				throw new ClydePhpException('Unable to add path '.$path.' to classloader: is not a dir or not exist');
			}
			
			if (!array_search($path,$this->classpath)) {
				array_unshift($this->classpath,$path);
			}
			 
			$dirs= glob($path.'/*',GLOB_ONLYDIR);
			if ($dirs)
				foreach ($dirs as $dir) {
					$this->addClassPath($dir);
				}
			
			return $this;			 
		}
	
		public function findFile($name) {			 
			foreach ($this->classpath as $path) {
				if (is_file($path.$name)) {
					return $path.$name;
				}
			}
			 
			return false;			 
		}
	
		public static function autoload($class_name) {
			if (class_exists($class_name) || $class_name == '') {
				return;
			}
			$classFile = ClassLoader::getInstance()->findFile('class.'.strtolower($class_name) . '.php');
			if ($classFile){
				require $classFile;
				return;
			}
	
			throw new ClydePhpException('Class '.$class_name.' does not exist. Check the classpath');	
		}	
	}
	/**
	 * simple function that wrap the classloader addClassPath method
	 *
	 * @param string $path
	 */
	function addClasspath($path) {
		ClassLoader::getInstance()->addClassPath($path);
	}
