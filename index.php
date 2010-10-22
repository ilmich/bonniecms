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
	
	if (!is_readable("data/pages/".$pageId.".metadata.php")) {
		$page = array("title" => "Pagina non trovata");
		$content = "La pagina che stai cercando non esiste";	
	}else {
		require_once "data/pages/".$pageId.".metadata.php";
		$content = new Template("data/pages/".$pageId.".php");
		$content = $content->render();
	}
		
	if (isset($page['template']))
		$template = $page['template'];
	else{
		$template = getTemplateName();	
	}
		
	$tpl = new Template("templates/".$template."/page.php");
	$tpl->fromArray($page);	
	$tpl->content= $content;
	$tpl->allowComments=false;
	$tpl->pageId = $pageId;
	
	EventManager::getInstance()->getEvent("onRender")->raise($req,$tpl);	
	$resp->setBody($tpl->render());
	
	EventManager::getInstance()->getEvent("processResponse")->raise($req,$resp);	
	$resp->send();	

?>