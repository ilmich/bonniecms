<?php	
		
	require_once "cmsclasses.php";	
	
	Config::getConfig()->site=require_once getDataDir()."config.php";;
	
	$conf = getCmsConfig();
	
	define ("WEB_ROOT",String::slash($conf["WEB_ROOT"]));
	define ("SITE_NAME",$conf["SITE_NAME"]);
	define ("SITE_SLOGAN",$conf["SITE_SLOGAN"]);	
	define ("DEFAULT_PAGE",$conf["DEFAULT_PAGE"]);

	$req = HttpRequest::getHttpRequest();
	
	//load lang
	if (!is_null($req->getParam('lang'))) {
		Lang::setLocale($req->getParam('lang'));
	}else {
		Lang::setLocale($conf['LANG']);
	}
	Lang::loadMessages("core");
	Lang::loadMessages("menu");
		
	//load all hooks
	$plugins = glob(APP_ROOT."/plugins/*.php");
	if ($plugins)
		foreach ($plugins as $file) {
			require_once $file;
		}		
		
	//load menus
	require_once(getDataDir()."menus.php");
	
	//register shutdown function
	register_shutdown_function("sessionEnd");
	
	Session::getInstance()->start("bonniecms");
	//launch session start event
	EventManager::getInstance()->getEvent("sessionStart")->raise();	
	
	function sessionEnd() {		
		EventManager::getInstance()->getEvent("sessionEnd")->raise();
	}
?>