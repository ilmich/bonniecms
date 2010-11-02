<?php

	require_once CLYDEPHP_VENDOR."pecora/pecora.php";

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