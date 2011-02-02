<?php	
		
	require_once "cmsclasses.php";	
		
	Cms::getCms()->configure()->loadPlugins()->startSession()->initLang();		
	
	Lang::loadMessages("core");
	Lang::loadMessages("menu");
	
	//load menus
	require_once(getDataDir()."menus.php");	
	
