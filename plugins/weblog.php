<?php if (!defined('CLYDEPHP')) die("Direct access not allowed");
	
	if (is_writable(getLogsDir())) {

		EventManager::getInstance()->getEvent("processRequest")->subscribe("logRequest");
	
		if (!file_exists(getLogsDir()."weblog")) {
			@mkdir(getLogsDir()."weblog");
		}
	}
	
	function logRequest($req) {		
				
		$fileName = getLogsDir()."weblog/".date('Y-m-d',time())."-weblog.log";		
		
		//lock file without polling or retry
		//because weblogging is not a critical point of the application
		//and skipping in case of lock failure is a reasonable thing		
		if (File::lock($fileName,null,null)) {			
			$row = time()."|".
					$req->getRemoteAddr()."|".$req->getUserAgent()."|".
					$req->getMethod()."|".$req->getReferer()."|".Url::fullUrl()."\n";
			file_put_contents($fileName,$row,FILE_APPEND);			
			File::release($fileName);
		}
	}
	