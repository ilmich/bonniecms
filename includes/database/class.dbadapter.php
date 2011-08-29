<?php if (!defined('CLYDEPHP')) die('Direct access not allowed') ;?>
<?php

	abstract class DbAdapter extends BaseClass {
		
		abstract public function getDatabaseType();
		
		abstract public function getAdapterName();
		
	}
