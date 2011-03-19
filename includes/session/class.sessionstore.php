<?php if (!defined('CLYDEPHP')) die('Direct access not allowed') ;?>
<?php

	interface SessionStore {
	
		public function open($savePath,$sessionName);
	
		public function close();
	
		public function read($id);
	
		public function write($id,$sessData);
	
		public function destroy($id);
	
		public function gc($maxLifeTime);
	
	}

