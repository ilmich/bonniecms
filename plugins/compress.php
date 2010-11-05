<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;

	EventManager::getInstance()->getEvent("processResponse")->subscribe("compressResponse");
	
	function compressResponse($req,$resp) {
		
		$accept = $req->getHeader("ACCEPT-ENCODING");
		$encoding = false;
		
		if (strpos($accept, 'x-gzip') !== false ){
        	$encoding = 'x-gzip';
		}
		
		if (strpos($accept, 'gzip') !== false ){
        	$encoding = 'gzip';
		}
		
		if (!$encoding) return;
				
		$resp->addHeader("Content-Encoding",$encoding);		 
        $body = gzencode($resp->getBody(), 9,FORCE_GZIP);		
		$resp->setBody($body);
		
	}