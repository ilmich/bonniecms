<?php	
		
	require_once "cmsclasses.php";	
	
	//load config files
	$confs = glob(getConfDir()."*.php");
	if ($confs)
		foreach ($confs as $conf) {
			$arr = require_once $conf; 
			if (is_array($arr)) {
				$name = basename($conf,".php");
				Config::getConfig()->$name = $arr;
			}
		}
		
	$conf = getCmsConfig();
	
	define ("WEB_ROOT",String::slash($conf["WEB_ROOT"]));
	define ("SITE_NAME",$conf["SITE_NAME"]);
	define ("SITE_SLOGAN",$conf["SITE_SLOGAN"]);	
	define ("DEFAULT_PAGE",$conf["DEFAULT_PAGE"]);	
		
	//load all hooks
	$plugins = glob(APP_ROOT."/plugins/*.php");
	if ($plugins)
		foreach ($plugins as $file) {
			require_once $file;
		}		

		//register shutdown function
	register_shutdown_function("sessionEnd");
	
	Session::getInstance()->start("bonniecms");
	//launch session start event
	EventManager::getInstance()->getEvent("sessionStart")->raise();		
		
	$req = HttpRequest::getHttpRequest();
	
	//raise processRequest event
	EventManager::getInstance()->getEvent("processRequest")->raise($req);
	
	//load lang
	if (!is_null($req->getParam('lang'))) {
		Lang::setLocale($req->getParam('lang'));
	}else {
		Lang::setLocale($conf['LANG']);
	}
	Lang::loadMessages("core");
	Lang::loadMessages("menu");
	
	//load menus
	require_once(getDataDir()."menus.php");	
	
	function sessionEnd() {		
		EventManager::getInstance()->getEvent("sessionEnd")->raise();
	}
