<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>

<?php
	
	EventManager::getInstance()->getEvent("onRender")->subscribe("selectTheme");

	function selectTheme($req,$tpl) {		
		if (!is_null($req->getParam('template'))) {
			$file = basename($tpl->getFileName());
			$tpl->setFileName("templates/".$req->getParam('template')."/".$file);
		}
	}