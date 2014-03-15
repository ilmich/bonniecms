<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	abstract class DbAdapter extends BaseClass {
		
		abstract public function getDatabaseType();
		
		abstract public function getAdapterName();
		
	}
