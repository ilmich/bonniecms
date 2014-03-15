<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	EventManager::getInstance()->getEvent('processResponse')->subscribe('compressResponse');
	
	function compressResponse($req,$resp) {		
		$encoding = $req->getCompressionEncoding();		
		
		if (!$encoding) 
			return;
		
		$resp->addHeader('Content-Encoding',$encoding)
			 ->compressBody();        		
	}