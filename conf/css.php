<?php
	
	$css['minify'] = true;
	$css['style'] = array("files" => array(getTemplateDir()."winterplain/style.css"),
						  "prependRelativePath" => getTemplateWebRoot()."winterplain/");
	
	return $css;