<?php


	//EventManager::getInstance()->getEvent("processRequest")->subscribe("getCache");
	//EventManager::getInstance()->getEvent("processResponse")->subscribe("putCache");
	
	function getCache($req) {
		$cache = Cache::getCache();		
	
		$cachedObj = $cache->get(HttpRequest::getHttpRequest()->getHeader("REQUEST-URI"));
	
		if (!is_null($cachedObj) && HttpRequest::getHttpRequest()->getMethod() === 'GET') {		
			echo $cachedObj;
			die();
		}
	}
	
	function putCache($res) {
		$cache = Cache::getCache();
		
		$cache->put(HttpRequest::getHttpRequest()->getHeader("REQUEST-URI"),$res->getBody());
	}