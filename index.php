<?php 

	require_once 'includes/master.inc.php';		
	
	$mainComp = getCmsConfig('DEFAULT_COMPONENT','site');	
	
	require_once $mainComp.'.php';
