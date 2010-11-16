<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php	
	
	$cacheTimeout=60; //timeout in seconds

	if (is_writable(getDataDir())) {
		EventManager::getInstance()->getEvent("processRequest")->subscribe("cacheRequest");
		EventManager::getInstance()->getEvent("processResponse")->subscribe("cacheResponse");
	}
	
	function cacheRequest($req) {
		
		$db = Database::getDatabase("cache");		
		
		$cacheid = $req->getHeader("REQUEST-URI");
		if (file_exists($db->table(true))) {
			if (File::lock($db->table(true),null,null)) {
				if ($row = $db->getRow($cacheid,false)) {	
					//is valid
					if ($row[$cacheid]['expiration']>time()) {							
						$resp = $row[$cacheid]['data'];										
						$resp->send();
						File::release($db->table(true));
						exit();
					}
				}	
				File::release($db->table(true));
			}
		}		
	}
	
	function cacheResponse($req,$resp) {
		
		global $cacheTimeout;
		
		$db = Database::getDatabase("cache");
		$cacheid = $req->getHeader("REQUEST-URI");
		
		if (File::lock($db->table(true),null,null)) {
			$db->insertRow(array($cacheid=>array("data"=>$resp,"expiration"=>time()+$cacheTimeout)),false);
			File::release($db->table(true));
		}		
	}
	