<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	interface SessionStore {
	
		public function open($savePath,$sessionName);
	
		public function close();
	
		public function read($id);
	
		public function write($id,$sessData);
	
		public function destroy($id);
	
		public function gc($maxLifeTime);
	
	}

