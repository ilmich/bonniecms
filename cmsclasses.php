<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }	
	
	require_once 'cmsfunctions.php';
	
	class DbSessionStore implements SessionStore {
		
		public function open($savePath,$sessionName) { 
			return true; 
		}

		public function close() { 
			return true; 
		}

		public function read($id) {			
			$db = Database::getDatabase('sessions');
			$db->lock();
			$data = @array_shift($db->getRow($id,false));
			$db->release();
			if (!$data) {
				return null;
			} 
			return $data;			 
		}

		public function write($id,$sessData) {			
			$db = Database::getDatabase('sessions');
			$db->lock();
			$db->insertRow(array($id => array($sessData)));
			$db->release();
			return true;			
		}

		public function destroy($id){			
			$db = Database::getDatabase('sessions');
			$db->lock();
			$db->deleteRow($id);
			$db->release();
			return true;			
		}

		public function gc($maxLifeTime) {			
			EventManager::getInstance()->getEvent('sessionGC')->raise($maxLifeTime);
			
			$db = Database::getDatabase('sessions');
			$db->lock();
			$db->refresh();
			$db->release();
			
			return true;			
		}	
	}