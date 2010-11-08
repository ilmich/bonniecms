<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;

	EventManager::getInstance()->getEvent("processResponse")->subscribe("compressResponse");
	
	function compressResponse($req,$resp) {
		
		$encoding = $req->getCompressionEncoding();		
		
		if (!$encoding) return;
		
		$resp->addHeader("Content-Encoding",$encoding)
			 ->compressBody();		 
        		
	}