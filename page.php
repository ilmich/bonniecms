<?php 

	require_once "includes/master.inc.php";		
	
	$req = Cms::getCms()->getHttpRequest();	
	
	$resp = new HttpResponse();	
	
	//compute page
	$pageId = String::slugify($req->getParam("page"));
		
	if (is_null($pageId) || $pageId === "") {
		$pageId = "home";
	}	
	
	$lang = Lang::getLocale();
	//check if the page is avaible on filesystem
	if (is_readable(getDataDir()."pages/".$pageId.".metadata.php") || is_readable(getDataDir()."pages/".$pageId.".".$lang.".metadata.php")){		
		//check for localized version
		if (is_readable(getDataDir()."pages/".$pageId.".".$lang.".metadata.php")) {
			$page = require_once getDataDir()."pages/".$pageId.".".$lang.".metadata.php";
			$contentFile = getDataDir()."pages/".$pageId.".".$lang.".php";		
		}else { 
			$page = require_once getDataDir()."pages/".$pageId.".metadata.php";
			//set the filename of the page		
			$contentFile = getDataDir()."pages/".$pageId.".php";
		}		
	}else {
		//try to find page in database	
		$db = Database::getDatabase("pages");
		
		if (file_exists($db->table(true))) {
			if ($row = $db->getRow(array($pageId.".".$lang,$pageId),false)) {
				//shift first result in order to show the translated page, if exists				
				$page = array_shift($row);				
			}
		}
		
		if (!isset($page)) {
			$page = array("showTitle" => true,
						  "title" => Lang::getMessage("PAGE_NOT_FOUND_TITLE"),
						  "content" => Lang::getMessage("PAGE_NOT_FOUND"));	
			$resp->setStatus(404);		
		}	
	}		
			
	if (isset($page["template"])) {
		//override template configuration setting
		$conf["TEMPLATE"] = $page["template"];
		Config::getConfig()->site = $conf;		
	}

	$template = getTemplateName();
		
	//load main template
	$tpl = loadTemplate("index.php",$template);
	if (is_null($tpl)) {		
		die ("Unable to load template for rendering");		
	}
	if (isset($page['meta'])) {
		$tpl->addMetaHeaders($page['meta']);
	}
	$tpl->fromArray($page);
	
	//if filesystem mode is activated, load and render the page
	if (isset($contentFile)) $tpl->content=$tpl->render($contentFile);
	
	//load and render the component template
	$tpl->mainBody = $tpl->render(findTemplate("page.php",$template));	
	
	$tpl->pageId = $pageId;
	
	EventManager::getInstance()->getEvent("onRender")->raise($req,$tpl);
	//render main template	
	$resp->setBody($tpl->render());
	
	Cms::getCms()->sendHttpResponse($resp);

