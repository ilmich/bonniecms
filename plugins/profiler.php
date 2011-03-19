<?php if (!defined('CLYDEPHP')) die('Direct access not allowed') ;

	$startTime=0;
	$memStart=0;
	
	EventManager::getInstance()->getEvent('processRequest')->subscribe('startProfiler');
	EventManager::getInstance()->getEvent('processResponse')->subscribe('endProfiler');	
	
	function startProfiler($res) {		
		global $startTime,$memStart;
		
		$startTime = microtime(true);
		$memStart = memory_get_usage();		
	}
	
	function endProfiler($req,$resp) {		
		global $startTime,$memStart;
		
		$timeElapsed = (float)(microtime(true)-$startTime);
		$memUsage = (float)(memory_get_usage() - $memStart);
		
		$body = $resp->getBody();		
		$body.="<center>Time elapsed ".$timeElapsed." | Memory usage ".bytes2str($memUsage)."</center>";
		
		$resp->setBody($body);		
	}
