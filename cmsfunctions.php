<?php

	define('MENU_LIST',1);
	define('MENU_SIMPLE',2);

	function getCmsConfig() {
		return Config::get("site");
	}
	
	function getDataDir() {
		return DOC_ROOT."data/";
	}
	
	function getTemplateName() {
		
		$conf = getCmsConfig();
		if (!isset($conf["TEMPLATE"]))
			return null;
			
		return $conf["TEMPLATE"];
	}

	function makeLink($id,$locale=null,$type="page") {
				
		$lang="";
		$conf = getCmsConfig();
		if (!is_null($locale) && $locale !== $conf['LANG'])
			$lang="&lang=".$locale;
		
		switch ($type) {
			case 'page':				
				return "index.php?page=".String::slugify($id).$lang;
			case 'download':
				 return "services/download.php?file=".$id;
			default:
				return null;
		}
		
	}
	
	function buildMenu($name,$type= MENU_LIST) {
		
		global $menu_list;
		
		$menu = $menu_list[$name];
		
		$out = "";		
		if ($type == MENU_LIST) $out = "<ul>";
		
		foreach ($menu as $key => $value) {
			switch ($type) {
				case MENU_LIST:
					$out .= "<li><a href='".$value[0]."'>".$value[1]."</a></li>";
					break;
				default:	
					$out .= "<a href='".$value[0]."'>".$value[1]."</a>";
			}				
		}
		
		if ($type == MENU_LIST) $out .= "</ul>";

		return $out;
	}
	
	function getComments($pageId) {
		
		$comm = new Comments();		
		return $comm->getComments($pageId);
	}
	
	function getHtmlHeaders() {
		$out = "";
		
		return $out;
	}
		
?>