<?PHP
class ClassLoader
{
	// Singleton object. Leave $me alone.
	private static $me;

	private $classpath = array();

	/**
	 * Standard singleton
	 * @return ClassLoader
	 */
	public static function getInstance()
	{
		if(is_null(self::$me))
		self::$me = new ClassLoader();
		return self::$me;
	}

	private function __construct()
	{
		 
	}

	public function addClassPath($path) {

		if ($path !== null && $path !== '') {
			$path = $this->_fixPath($path);

			if (!array_search($path,$this->classpath)) {
				array_unshift($this->classpath,$path);
			}
		}
		 
		if (!file_exists($path) || !is_dir($path)) {
			throw new ClydePhpException("Unable to add path $path to classloader: is not a dir or not exist");
		}
		 
		foreach ($this->_getDirs($path) as $dir) {
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

	//get the list of directory contained in a path
	private function _getDirs($path) {
		 
		$path = $this->_fixPath($path);
		 
		$dirs = array();
		 
		$dh  = opendir($path);
		while (false !== ($filename = readdir($dh))) {
			if ($filename != "." && $filename != "..") {
				if (is_dir($path.$filename)) {
					$dirs[]=$path.$filename;
				}
			}
		}
		closedir($dh);

		return $dirs;
	}

	private function _fixPath($path) {
		 
		return String::slash($path);
	}

	public static function autoload($class_name)
	{
		 
		$classFile = ClassLoader::getInstance()->findFile("class.".strtolower($class_name) . '.php');
		if ($classFile){
			require $classFile;
			return;
		}

		throw new ClydePhpException("Class ".$class_name." does not exist. Check the classpath");

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
