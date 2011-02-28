<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;

	require_once CLYDEPHP_VENDOR."minify/HTML.php";

	EventManager::getInstance()->getEvent("processResponse")->subscribe("minifyResponse");
	
	function minifyResponse($req,$resp) {
		
		if ($resp->getMimeType() === 'text/html')
			$resp->setBody(Minify_HTML::minify($resp->getBody()));		 
        		
	}