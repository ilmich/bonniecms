<?php if (!defined('CLYDEPHP')) die('Direct access not allowed');
	
	if (is_writable(getLogsDir())) {
		EventManager::getInstance()->getEvent('processRequest')->subscribe('logRequest');
	
		if (!file_exists(getLogsDir().'weblog')) {
			@mkdir(getLogsDir().'weblog');
		}
	}
	
	function logRequest($req) {		
		$fileName = getLogsDir().'weblog/'.date('Y-m-d',time()).'-weblog.log';		
		
		//lock file without polling or retry
		//because weblogging is not a critical point of the application
		//and skipping in case of lock failure is a reasonable thing		
		if (File::lock($fileName,null,1)) {			
			$row = time().'|'.
					$req->getRemoteAddr().'|'.$req->getUserAgent().'|'.
					$req->getMethod().'|'.$req->getReferer().'|'.Url::fullUrl()."\n";
			file_put_contents($fileName,$row,FILE_APPEND);			
			File::release($fileName);
		}
		else {
			//but show a warn because failing to write weblogging slow down cms total speed
			echo '<div style=\'color: #ff0000;\'><b>WARN:Weblogging failed because the log dir is not writable. Fix this, or disable weblogging plugin!!!</b></div>';
		}
	}
	