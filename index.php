<?php 

	require_once "includes/master.inc.php";		
		
	$req = HttpRequest::getHttpRequest();	
	$resp = new HttpResponse();
	
	EventManager::getInstance()->getEvent("processRequest")->raise($req);	
	//compute page
	$pageId = String::slugify($req->getParam("page"));	
	if (is_null($pageId) || $pageId === "") {
		$pageId = DEFAULT_PAGE;
	}	
	
	//check if the page is avaible on filesystem
	if (is_readable("data/pages/".$pageId.".metadata.php")) {
		require_once "data/pages/".$pageId.".metadata.php";
		//set the filename of the page		
		$contentFile = "data/pages/".$pageId.".php";		
	}else {
		//try to find page in database	
		$db = Database::getDatabase("pages");
		
		if (file_exists($db->table(true))) {
			if ($row = $db->getRow($pageId,false)) {
				$page = array("title" => $row[$pageId]["title"]);
				$page["content"] = $row[$pageId]["content"];
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

?>