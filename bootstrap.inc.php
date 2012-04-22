<?php if (!defined('CLYDEPHP')) die('Direct access not allowed') ;?>
<?php	
		
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
	
	