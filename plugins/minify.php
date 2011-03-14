<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;

	require_once CLYDEPHP_VENDOR."minify/HTML.php";
	require_once CLYDEPHP_VENDOR."minify/CSS.php";
		
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