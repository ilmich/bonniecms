<?php 

	require_once "includes/master.inc.php";		
		
	$resp = new HttpResponse();	
	
	//compute page
	$pageId = String::slugify($req->getParam("page"));
		
	if (is_null($pageId) || $pageId === "") {
		$pageId = DEFAULT_PAGE;
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
			$page = array("title" => Lang::getMessage("PAGE_NOT_FOUND_TITLE"),
						  "content" => Lang::getMessage("PAGE_NOT_FOUND"));			
		}	
	}		
		
	$template = getTemplateName();
	
	if (isset($page["template"]))
		$template = $page["template"];	
		
	$tpl = new Template("templates/".$template."/page.php");
	$tpl->fromArray($page);
	
	//if filesystem mode is activated, load and render the page
	if (isset($contentFile)) $tpl->content=$tpl->render($contentFile);
	
	$tpl->pageId = $pageId;
	
	EventManager::getInstance()->getEvent("onRender")->raise($req,$tpl);	
	$resp->setBody($tpl->render());
	
	EventManager::getInstance()->getEvent("processResponse")->raise($req,$resp);	
	$resp->send();	

