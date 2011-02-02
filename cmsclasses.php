<?php

	require_once CLYDEPHP_VENDOR."pecora/pecora.php";
	require_once "cmsfunctions.php";

	class Cms {
		
		private static $me=null;		
		
		private function __construct() {
			
		}
		
		public static function getCms() {
			if(is_null(self::$me)) {
				self::$me = new Cms();
			}
			
			return self::$me;
		}		
		
		public function configure() {
			
			//load config files
			$confs = glob(getConfDir()."*.php");
			if ($confs)
				foreach ($confs as $conf) {
					$arr = require_once $conf; 
					if (is_array($arr)) {
						$name = basename($conf,".php");
						Config::getConfig()->$name = $arr;
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
		
	}
	
	class Lang {
		
		private $locale = "en";		
		private $messages = array();
		private static $me=null;
		
		private function __construct() {
			
		}
		
		private function getLang() {
			if(is_null(self::$me)) {
				self::$me = new Lang();
			}
			
			return self::$me;
		}
		
		public static function setLocale($locale) {
			self::getLang()->locale=$locale;		
		}
		
		public static function getLocale() {
			return self::getLang()->locale;			
		}
		
		public static function loadMessages($name) {
			$instance = self::getLang();
			$filename = APP_ROOT."lang/".$instance->locale."/".$name.".php";
			if (!is_readable($filename)) {
				return false;
			}			
			$instance->messages = array_merge($instance->messages,require_once ($filename));	
		}
		
		public static function getMessage($key){
			$messages = self::getLang()->messages;
			if (!isset($messages[$key])) {
				return $key;		
			}
			return $messages[$key];
		}
		
	}
	
	class Database {
				
		private static $me=array();		
		
		public function getDatabase($name) {
			if(!isset(self::$me[$name])) {
				self::$me[$name] = new Pecora(getDataDir(),$name);
			}
			
			return self::$me[$name];
		}
		
	}
	
	class DbSessionStore implements SessionStore {
		
		public function open($savePath,$sessionName) { return true; }

		public function close() { return true; }

		public function read($id) {
			
			$db = Database::getDatabase("sessions");
			$db->lock();
			$data = @array_shift($db->getRow($id,false));
			$db->release();
			if (!$data) {
				return null;
			} 
			return $data;
			 
		}

		public function write($id,$sessData) {
			
			$db = Database::getDatabase("sessions");
			$db->lock();
			$db->insertRow(array($id => array($sessData)));
			$db->release();
			return true;
			
		}

		public function destroy($id){
			
			$db = Database::getDatabase("sessions");
			$db->lock();
			$db->deleteRow($id);
			$db->release();
			return true;
			
		}

		public function gc($maxLifeTime) {
			
			EventManager::getInstance()->getEvent("sessionGC")->raise($maxLifeTime);
			
			$db = Database::getDatabase("sessions");
			$db->lock();
			$db->refresh();
			$db->release();
			
			return true;
			
		}
	
	}