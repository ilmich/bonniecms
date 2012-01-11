<?php
	
	$css['minify'] = false;
	$css['etag'] = false;
	$css['style'] = array("files" => array(getTemplateDir()."winterplain/style.css"),
						  "prependRelativePath" => getTemplateWebRoot('winterplain'));
	
	return $css;