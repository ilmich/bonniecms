<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php	
	
	$cacheTimeout=10; //timeout in seconds

	if (is_writable(getDataDir())) {
		EventManager::getInstance()->getEvent("processRequest")->subscribe("cacheRequest");
		EventManager::getInstance()->getEvent("processResponse")->subscribe("cacheResponse");
	}
	
	function cacheRequest($req) {
		
		$db = Database::getDatabase("cache");
		
		$cacheid = $req->getHeader("REQUEST-URI");
		if (file_exists($db->table(true))) {
			if ($db->lock()) {
				if ($row = $db->getRow($cacheid,false)) {
					//is valid
					if ($row[$cacheid]['expiration']>time()) {							
						$resp = $row[$cacheid]['data'];				
						$resp->send();
						$db->release();
						exit();
					}
				}	
				$db->release();
			}
		}		
	}
	
	function cacheResponse($req,$resp) {
		
		global $cacheTimeout;
		
		$db = Database::getDatabase("cache");
		$cacheid = $req->getHeader("REQUEST-URI");
		if ($db->lock()) {
			$db->insertRow(array($cacheid=>array("data"=>$resp,"expiration"=>time()+$cacheTimeout)),false);
			$db->release();
		}		
	}
	