<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }	
		
	require_once 'cmsfunctions.php';	
	
	//add cms classpath
	addClasspath(DOC_ROOT.'classes/');
	
	//init cms
	Cms::init();	
	
	Cms::startSession();
	
	//load lang files
	Lang::loadMessages('core');
	Lang::loadMessages('menu');
	
	//load menus
	require_once(getDataDir().'menus.php');	
	
	