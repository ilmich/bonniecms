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
	
	if (is_readable("data/pages/".$pageId.".metadata.php")) {
		require_once "data/pages/".$pageId.".metadata.php";
		$content = new Template("data/pages/".$pageId.".php");
		$page["content"] = $content->render();	
	}else {
		//try to find page in database	
		$db = Database::getDatabase("pages");
	
		if ($row = $db->getRow($pageId,false)) {
			$page = array("title" => $row[$pageId]["title"]);
			$page["content"] = $row[$pageId]["content"];
		}
		else {
			$page = array("title" => Lang::getMessage("PAGE_NOT_FOUND_TITLE"),
						  "content" => Lang::getMessage("PAGE_NOT_FOUND"));			
		}	
	}		
		
	$template = getTemplateName();
	
	if (isset($page["template"]))
		$template = $page["template"];	
		
	$tpl = new Template("templates/".$template."/page.php");
	$tpl->fromArray($page);
	$tpl->pageId = $pageId;
	
	EventManager::getInstance()->getEvent("onRender")->raise($req,$tpl);	
	$resp->setBody($tpl->render());
	
	EventManager::getInstance()->getEvent("processResponse")->raise($req,$resp);	
	$resp->send();	

?>