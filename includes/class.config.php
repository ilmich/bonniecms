<?PHP

class ConfigException extends ClydePhpException {

}
// The Config class provides a single object to store your application's settings.

class Config
{
	 
	// Singleton object. Leave $me alone.
	private static $me;

	// Add your server hostnames to the appropriate arrays. ($_SERVER['HTTP_HOST'])
	private $productionServers = array();
	private $stagingServers    = array();
	private $localServers      = array('localhost','localhost:8516');

	private $configData = array();

	// Singleton constructor
	private function __construct()
	{
			
	}

	public function __get($key) {
		return array_key_exists($key,$this->configData) ? $this->configData[$key] : null;
	}

	public function __set($key, $value) {
		$this->configData[$key] = $value;
	}

	public function __isset($key) {
		return isset($this->configData[$key]);
	}

	public function whereAmI()
	{
		if(in_array($_SERVER['HTTP_HOST'], $this->productionServers))
		return 'production';
		elseif(in_array($_SERVER['HTTP_HOST'], $this->stagingServers))
		return 'staging';
		elseif(in_array($_SERVER['HTTP_HOST'], $this->localServers))
		return 'local';
		elseif(isset($_ENV['SHELL']))
		return 'shell';
		else
		throw new ConfigException("You need to setup your server name: \$_SERVER['HTTP_HOST\] reported ".$_SERVER['HTTP_HOST']);
	}

	private function _parseIniFile($path,$sections) {
		 
		if (!is_readable($path)) {
			throw new ClydePhpException("Config file ".$path." not readable");
		}
		 
		$load = @parse_ini_file($path,$sections);
		if ($load === false) {
			return false;
		}
		$this->configData = array_merge($this->configData,$load);
		return true;
	}

	/**
	 * Standard singleton
	 * @return Config
	 */
	public static function getConfig()
	{
		if(is_null(self::$me))
			self::$me = new Config();
		return self::$me;
	}

	public static function parseIniFile($path,$sections=true)
	{
		return self::getConfig()->_parseIniFile($path,$sections);
	}

	// Allow access to config settings statically.
	// Ex: Config::get('some_value')
	public static function get($key)
	{
		return self::getConfig()->$key;
	}

}
