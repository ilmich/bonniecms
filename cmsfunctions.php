<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	define('BONNIECMS_VERSION','0.5.0');

	define('MENU_LIST',1);
	define('MENU_SIMPLE',2);
	
	require_once CLYDEPHP_VENDOR.'minify/HTML.php';
	require_once CLYDEPHP_VENDOR.'minify/CSS.php';	

	/**
	 * Useful alias functions for template designer, that reduce php
	 * code in template
	 * 
	*/	
	function __text($key) {
		echo Lang::getMessage($key);
	}
	
	function __webRoot() {
		echo String::slash(getCmsConfig('WEB_ROOT'));
	}
	
	function __webRootTemplate($templateName = null) {
		echo getTemplateWebRoot($templateName);
	}
	
	function __siteName() {
		echo getCmsConfig('SITE_NAME');
	}
	
	function __siteSlogan() {
		echo getCmsConfig('SITE_SLOGAN');
	}
	
	function __conf($key,$context='site') {
		echo getCmsConfig($key,$context);
	}
	
	function __url($id,$locale=null,$type='page') {
		echo makeLink($id,$locale,$type);
	}
	
	function __meta($name,$content) {
		echo HTML::meta($name,$content);
	}
		
	function __anchor($url='#',$text=null,$extra='') {
		echo HTML::anchor($url,$text,$extra);
	}
		
	function __image($src='#',$extra='') {
		echo HTML::image($src,$extra);
	}
	
	function __renderStaticBlock($name) {		
		$tpl = new Template(getBlocksDir().$name.'.php');		
		echo $tpl->render();
	}
	
	function __sendEvent($name) {
		EventManager::getInstance()->getEvent($name)->raise();
	}
	
	/**
	 * common functions 
	 * 
	 * 
	 */
	function getWebRoot() {
		return String::slash(getCmsConfig('WEB_ROOT'));
	}

	function getSiteName() {
		return getCmsConfig('SITE_NAME');
	}
	
	function getSiteSlogan() {
		return getCmsConfig('SITE_SLOGAN');
	}
	
	function getCmsConfig($key=null,$context='site') {		
		return Cms::getConfigKey($key,$context);
	}
	
	/**
	 * Get data dir path
	 * 
	 * @param $absolute retrieve absolute data dir or relative data dir
	 */
	function getDataDir($absolute=false) {
		if ($absolute) {
			return DOC_ROOT.'data/';
		}
		return APP_ROOT.'data/';
	}
	
	function getBlocksDir($absolute=false) {
		return getDataDir($absolute).'blocks/';
	}
	
	/**
	 * Get logs dir path
	 * 
	 * @param $absolute retrieve absolute logs dir or relative logs dir
	 */
	function getLogsDir($absolute=false) {
		return getDataDir($absolute).'logs/';
	}
	
	/**
	 * Get conf dir path
	 * 
	 * @param $absolute retrieve absolute conf dir or relative conf dir
	 */
	function getConfDir($absolute=false) {
		$env = $_SERVER['HTTP_HOST'];
		
		$base = APP_ROOT;		
		if ($absolute){
			$base = DOC_ROOT;
		}
		
		//if exist and is readable, return specific server configuration dir
		if (is_readable($base.'conf_'.$env.'/')) {				
			return $base.'conf_'.$env.'/';
		}		
		//else return standard configuration dir
		return $base.'conf/';
	}
	
	function getPluginsDir() {
		return DOC_ROOT.'plugins/';
	}
	
	function getTemplateDir($tplName=null) {
		if (is_null($tplName)) {
			return DOC_ROOT.'templates/';
		}
		
		return DOC_ROOT.'templates/'.$tplName.'/';	
	}

	function getTemplateWebRoot($tplName=null) {
		if (is_null($tplName)) {
			$tplName = getCmsConfig('TEMPLATE');
		}
		
		return getWebRoot().'templates/'.$tplName.'/';	
	}
	
	function getTemplateName() {		
		$conf = getCmsConfig();
		if (!isset($conf['TEMPLATE']))
			return null;
			
		return $conf['TEMPLATE'];
	}
	
	function loadTemplate($file,$tplName=null) {		
		$tFile = findTemplate($file,$tplName);
		if (is_null($tFile)) return null;
				
		return new HtmlTemplate($tFile);		
	}
	
	function findTemplate($file,$tplName) {
			if ( (is_null($tplName) || $tplName === '') &&
			 (is_null($file) || $file === '')) 
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

	function makeLink($id,$locale=null,$type='page') {			
		$webRoot = getWebRoot();
		$lang="";
		$conf = getCmsConfig();
		if (!is_null($locale) && $locale !== $conf['LANG'])
			$lang='&lang='.$locale;
		
		switch ($type) {
			case 'page':				
				return $webRoot.'page.php?page='.String::slugify($id).$lang;
			case 'download':
				 return $webRoot.'services/download.php?file='.$id;
			case 'css':
				 return $webRoot.'css.php?css='.$id;
			default:
				return null;
		}		
	}
	
	function buildMenu($name,$type= MENU_LIST) {		
		global $menu_list;
		
		$menu = $menu_list[$name];		
		$out = '';		
		if ($type == MENU_LIST) $out = '<ul>';
		
		foreach ($menu as $key => $value) {
			switch ($type) {
				case MENU_LIST:
					$out .= '<li><a href=\''.$value['url'].'\'>'.$value['text'].'</a></li>';
					break;
				default:	
					$out .= '<a href=\''.$value['url'].'\'>'.$value['text'].'</a>';
			}				
		}
		
		if ($type == MENU_LIST) $out .= '</ul>';

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
	
	function minifyHtml($html=null) {		
		if (!is_null($html) && is_string($html))
			return Minify_HTML::minify($html);

		return null;        		
	}
	
	function minifyCss($css,$options=array()) {		
		if (!is_null($css) && is_string($css))
			return Minify_CSS::minify($css,$options);

		return null;		
	}