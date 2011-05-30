<?php if (!defined('CLYDEPHP')) die('Direct access not allowed') ;?>
<?php
	
	class Cms {
				
		private static $_cache = null; //cache manager
		private static $_template = null;				
		
		/**
		 * Load main configuration file and all files in conf/ dir
		 * 
		 */
		public static function configure($basePath=null) {			
			$env = $_SERVER['HTTP_HOST'];			
			$glob = Config::getConfig();
			if(!$basePath) {
				$basePath = APP_ROOT;
			}
			
			//check main config file site.php
			if (!is_readable($basePath.'/configuration.inc.php') && !is_readable($basePath.'/configuration.'.$env.'.inc.php')) {
				throw new ClydePhpException('Unable to load main configuration file');
			}

			//load main configuration
			if (is_readable($basePath.'/configuration.'.$env.'.inc.php')) {
				$arr = require_once $basePath.'/configuration.'.$env.'.inc.php';
			}else {
				$arr = require_once $basePath.'/configuration.inc.php';	
			}
			
			if (is_array($arr)) {				
				$glob->site = $arr;				
			}			
			
			//load config files
			$confs = glob(getConfDir().'*.php');
						
			//load all configuration files
			if ($confs)
				foreach ($confs as $conf) {			
					$arr = require_once $conf; 
					if (is_array($arr)) {
						$name = basename($conf,'.php');
						$glob->$name = $arr;
					}
				}					
		}	
		
		/**
		 * Load all configured plugins 
		 * 
		 */
		public function loadPlugins() {			
			if (is_array(Config::get('plugins'))) {
				foreach (Config::get('plugins') as $plugin) {
					require_once getPluginsDir().$plugin.'.php';
				}
			}			
		}
		
		/**
		 * Initialize language system
		 * 
		 */
		public static function initLang() {
			$glob = Config::getConfig();			
			//load lang
			if (!is_null(self::getHttpRequest()->getParam('lang'))) {
				Lang::setLocale(self::getHttpRequest()->getParam('lang'));
			}else {
				Lang::setLocale($glob->site['LANG']);
			}						
		}		
		
		/**
		 * Initialize cms
		 * 
		 */
		public static function init() {			
			
			self::configure();
			self::loadPlugins();
			self::initLang();
			self::startSession();

			//set active template
			if (!is_null(self::getHttpRequest()->getParam('template'))) {
				self::$_template = self::getHttpRequest()->getParam('template');
			}else {
				self::$_template = getCmsConfig('TEMPLATE');
			}
			
			//configure cache
			if (getCmsConfig("CACHE")) {
				self::$_cache = Cache::factory(array('type' => 'file', 'path' => getDataDir().'/cache/', 'expiration' => getCmsConfig('CACHE_TIME')));
			}						
			
			//raise processRequest event
			EventManager::getInstance()->getEvent('processRequest')->raise(self::getHttpRequest());						
		}
		
		/**
		 * Start cms session
		 * 
		 * @param string $name the session name
		 */
		public static function startSession($name='bonniecms') {			
			//register shutdown function
			register_shutdown_function(array('Cms','endSession'));
			
			Session::getInstance()->start($name);
			//launch session start event
			EventManager::getInstance()->getEvent('sessionStart')->raise();			
		}
		
		/**
		 * Put value in session
		 * 
		 * @param string $key
		 * @param mixed_type $value
		 */
		public static function putInSession($key,$value) {		
			Session::getInstance()->setValue($key,$value);		
		}
		
		/**
		 * Get value from session
		 * 
		 * @param string $key
		 * @return mixed_type the value
		 */
		public static function getFromSession($key) {
			return Session::getInstance()->getValue($key);
		}
		
		/**
		 * End cms session
		 * 
		 */
		public static function endSession() {
			EventManager::getInstance()->getEvent('sessionEnd')->raise();
		}
		
		/**
		 * Get http request
		 * 
		 * @return HttpRequest the current request
		 */
		public static function getHttpRequest() {		
			return HttpRequest::getHttpRequest();
		}
		
		/**
		 * Send http response
		 * 
		 * @param HttpResponse $res the response to send
		 */
		public static function sendHttpResponse($res) {			
			EventManager::getInstance()->getEvent('processResponse')->raise(self::getHttpRequest(),$res);		
			
			$res->send();			
		}
		
		/**
		 * Check if exist in cache a response for current url request
		 *
		 * @return HttpResponse the cached response or null otherwise
		 */
		public static function getCachedHttpResponse() {			
			if (!is_null(self::$_cache)) {
				$res = self::$_cache->get(self::getHttpRequest()->getRequestUri());				
				
				if (!is_null($res) && $res instanceof HttpResponse) {
					return $res;
				}
			}			
			return null;			
		}
		
		/**
		 * Put in cache the response
		 * 
		 * @param HttpResponse $res the response to cache
		 */
		public static function setCachedHttpResponse($res) {
			if (!is_null(self::$_cache)) {
				self::$_cache->put(self::getHttpRequest()->getRequestUri(),$res);
			}
		}
		
		/**
		 * Get cms cache manager
		 * 
		 * @return mixed_object the cache manager		 
		 */
		public static function getCacheManager() {			
			return 	self::$_cache;
		}		
		
		public static function loadTemplate($name) {
			return loadTemplate($name,self::$_template);
		}
	}