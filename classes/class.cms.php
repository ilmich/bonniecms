<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php

	class Cms {
		
		private static $me=null;
		private $_cache = null; //cache manager
				
		private function __construct() {
			
		}
		
		public static function getCms() {
			if(is_null(self::$me)) {
				self::$me = new Cms();
			}
			
			return self::$me;
		}		
		
		public function configure() {
			
			$env = $_SERVER['HTTP_HOST'];
			
			$glob = Config::getConfig();
			
			//check main config file site.php
			if (!is_readable(APP_ROOT."/configuration.inc.php") && !is_readable(APP_ROOT."/configuration.".$env.".inc.php")) {
				throw new ClydePhpException("Unable to load main configuration file");
			}

			//load main configuration
			if (is_readable(APP_ROOT."/configuration.".$env.".inc.php")) {
				$arr = require_once APP_ROOT."/configuration.".$env.".inc.php";
			}else {
				$arr = require_once APP_ROOT."/configuration.inc.php";	
			}
			
			if (is_array($arr)) {				
				$glob->site = $arr;				
			}			
			
			//load config files
			$confs = glob(getConfDir()."*.php");
						
			//load all configuration files
			if ($confs)
				foreach ($confs as $conf) {			
					$arr = require_once $conf; 
					if (is_array($arr)) {
						$name = basename($conf,".php");
						$glob->$name = $arr;
					}
				}
			return $this;		
		}	
		
		public function loadPlugins() {
			
			if (is_array(Config::get("plugins"))) {
				foreach (Config::get("plugins") as $plugin) {
					require_once getPluginsDir().$plugin.".php";
				}
			}
			return $this;
		}
		
		public function initLang() {
			
			//load lang
			if (!is_null($this->getHttpRequest()->getParam('lang'))) {
				Lang::setLocale($this->getHttpRequest()->getParam('lang'));
			}else {
				$conf = getCmsConfig();
				Lang::setLocale($conf['LANG']);
			}
			
			return $this;
			
		}
		
		public function init() {
			
			$this->configure()->loadPlugins()->initLang()->startSession();
			
			
			//configure cache
			if (getCmsConfig("CACHE")) {
				$this->_cache = Cache::factory(array("type" => "file", "path" => APP_ROOT."/cache/", "expiration" => getCmsConfig("CACHE_TIME")));			
			}						
			
			//raise processRequest event
			EventManager::getInstance()->getEvent("processRequest")->raise($this->getHttpRequest());
						
		}
		
		public function startSession($name="bonniecms") {
			
			//register shutdown function
			register_shutdown_function(array($this,"endSession"));
			
			Session::getInstance()->start($name);
			//launch session start event
			EventManager::getInstance()->getEvent("sessionStart")->raise();
			
			return $this;
		}
		
		public function endSession() {
			EventManager::getInstance()->getEvent("sessionEnd")->raise();
		}
		
		public function getHttpRequest() {		
			return HttpRequest::getHttpRequest();
		}
		
		public function sendHttpResponse($res) {
			
			EventManager::getInstance()->getEvent("processResponse")->raise($this->getHttpRequest(),$res);		
			
			$res->send();
			
		}
		
		public function getCachedHttpResponse($res) {
			
			if (!is_null($this->_cache)) {
				$res = $this->_cache->get($this->getHttpRequest()->getRequestUri());				
				
				if (!is_null($res) && $res instanceof HttpResponse) {
					return $res;
				}
			}
			
			return null;
			
		}
		
		public function setCachedHttpResponse($res) {

			if (!is_null($this->_cache)) {
				$this->_cache->put($this->getHttpRequest()->getRequestUri(),$res);
			}
			
			return $this;
		}
		
		public function getCacheManager() {			
			return 	$this->_cache;
		}
		
	}