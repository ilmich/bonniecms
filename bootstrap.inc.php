<?php	
	
	require_once "cmsfunctions.php";
	
	//require_once "test_code.php";	
	//load cms config
	if (!Config::parseIniFile(APP_ROOT."/config.ini")){
		throw new Exception("No configuration file found");
	}
	
	$conf = getCmsConfig();
	
	define ("WEB_ROOT",String::slash($conf['WEB_ROOT']));
	define ("SITE_NAME",$conf['SITE_NAME']);
	define ("SITE_SLOGAN",$conf['SITE_SLOGAN']);	
	define ("DEFAULT_PAGE",$conf['DEFAULT_PAGE']);
	
	//load all hooks
	$plugins = glob(APP_ROOT."/plugins/*.php");
	if ($plugins)
		foreach ($plugins as $file) {
			require_once $file;
		}
	
	//load menus
	require_once(APP_ROOT."/data/menus.php");
	
	//register shutdown function
	register_shutdown_function("sessionEnd");
	
	Session::getInstance()->start();
	
	//launch session start event
	EventManager::getInstance()->getEvent("sessionStart")->raise();	
	
	function sessionEnd() {		
		EventManager::getInstance()->getEvent("sessionEnd")->raise();
	}


?>