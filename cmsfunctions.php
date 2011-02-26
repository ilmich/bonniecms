<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php

	define('MENU_LIST',1);
	define('MENU_SIMPLE',2);

	/**
	 * Useful alias functions for template designer, that reduce php
	 * code in template
	 * 
	*/
	function __text($key) {
		echo Lang::getMessage($key);
	}
	
	function __webRoot() {
		echo String::slash(getCmsConfig("WEB_ROOT"));
	}
	
	function __siteName() {
		echo getCmsConfig("SITE_NAME");
	}
	
	function __siteSlogan() {
		echo getCmsConfig("SITE_SLOGAN");
	}
	
	function __conf($key,$context="site") {
		echo getCmsConfig($key,$context);
	}
	
	function __url($id,$locale=null,$type="page") {
		echo makeLink($id,$locale,$type);
	}
	
	function __meta($name,$content) {
		echo HTML::meta($name,$content);
	}
		
	function __anchor($url="#",$text=null,$extra="") {
		echo HTML::anchor($url,$text,$extra);
	}
		
	function __image($src="#",$extra="") {
		echo HTML::image($src,$extra);
	}
	
	/**
	 * common functions 
	 * 
	 * 
	 */
	function getWebRoot() {
		return String::slash(getCmsConfig("WEB_ROOT"));
	}
	
	function getSiteName() {
		return getCmsConfig("SITE_NAME");
	}
	
	function getSiteSlogan() {
		return getCmsConfig("SITE_SLOGAN");
	}
	
	function getCmsConfig($key=null,$context="site") {
		
		$conf = Config::get($context);
		if (is_null($key)) {
			return $conf;
		}
		if (isset($conf[$key])) {
			return $conf[$key];
		} 
		return null;
	}
	
	function getDataDir() {
		return APP_ROOT."data/";
	}
	
	function getLogsDir() {
		return APP_ROOT."logs/"; 
	}
	
	function getConfDir() {
		$env = $_SERVER['HTTP_HOST'];
		
		//if exist and is readable, return specific server configuration dir
		if (is_readable(APP_ROOT."conf_".$env."/")) {				
			return APP_ROOT."conf_".$env."/";
		}		
		//else return standard configuration dir
		return APP_ROOT."conf/";
	}
	
	function getPluginsDir() {
		return DOC_ROOT."plugins/";
	}
	
	function getTemplateDir($tplName=null) {
		if (is_null($tplName)) {
			return DOC_ROOT."templates/";
		}
		
		return DOC_ROOT."templates/".$tplName."/";	
	}	
	
	function getTemplateName() {		
		$conf = getCmsConfig();
		if (!isset($conf["TEMPLATE"]))
			return null;
			
		return $conf["TEMPLATE"];
	}
	
	function loadTemplate($file,$tplName=null) {		
		$tFile = findTemplate($file,$tplName);
		if (is_null($tFile)) return null;
				
		return new HtmlTemplate($tFile);		
	}
	
	function findTemplate($file,$tplName) {
			if ( (is_null($tplName) || $tplName === "") &&
			 (is_null($file) || $file === "")) 
			return null;
			
		//try to load first in the template dir
		if (is_readable(getTemplateDir($tplName).$file)) {
			return getTemplateDir($tplName).$file;
		}
		
		//try to load in the root template dir
		if (is_readable(getTemplateDir().$file)) {
			return getTemplateDir().$file;
		}
		
		return null;	
	}

	function makeLink($id,$locale=null,$type="page") {			
			
		$webRoot = getWebRoot();
		$lang="";
		$conf = getCmsConfig();
		if (!is_null($locale) && $locale !== $conf['LANG'])
			$lang="&lang=".$locale;
		
		switch ($type) {
			case 'page':				
				return Url::encode($webRoot."page.php?page=".String::slugify($id).$lang);
			case 'download':
				 return $webRoot."services/download.php?file=".$id;
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
					$out .= "<li><a href='".$value['url']."'>".$value['text']."</a></li>";
					break;
				default:	
					$out .= "<a href='".$value['url']."'>".$value['text']."</a>";
			}				
		}
		
		if ($type == MENU_LIST) $out .= "</ul>";

		return $out;
	}
	
	/**
	 * Return the choosen menu
	 * 
	 * @param $name menu name
	 * @return array list of menu items
	 */
	function getMenu($name) {
		
		global $menu_list;
		
		if (isset($menu_list[$name]))
			return $menu_list[$name];
			
		return array();
		
	}
	
	/**
	 * Check if an menu item is selected
	 *  
	 * @param $menu the menu to inspect
	 * @param $item the menu item to check
	 * @return bool true or false if the menu item is selected or not
	 */
	function checkCurrentMenuUrl($menu,$item) {
				
		global $menu_list;
		
		if (isset($menu_list[$menu]) && isset($menu_list[$menu][$item]))
			return $menu_list[$menu][$item]['url'] === Url::fullUrl();

		return false;
	}
	
	/**
	 * Get the key of the menu item selected
	 * 
	 * 
	 * @param $menu the menu to inspect
	 * @return mixed the menu item select or false otherwise
	 */
	function getCurrentMenuItem($menu) {
		
		global $menu_list;
		$currentUrl = Url::fullUrl();
		
		if (isset($menu_list[$menu])) {
			foreach ($menu_list[$menu] as $key => $value) {
				if ($currentUrl === $value['url']) 
					return $key;						
			}
		}

		return false;
	}