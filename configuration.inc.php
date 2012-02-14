<?php


	$site['WEB_ROOT'] = (isset($_SERVER['HTTPS'])) ? 	'https://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']) : 
														'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']);
	$site['SITE_NAME'] = "BonnieCMS SampleSite";
	$site['SITE_SLOGAN'] = "...keep it simple...";	
	$site['TEMPLATE'] = "winterplain";	
	$site['LANG']="en";
	$site['DEFAULT_COMPONENT'] = "page";
	$site['CACHE'] = true; //caching
	$site['CACHE_TIME'] = 5; //cache timeout in seconds
	$site['MINIFY'] = true;	
	
	return $site;