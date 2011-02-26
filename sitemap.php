<?php

	require_once "includes/master.inc.php";
	
	$res = new HttpResponse();
	//array of site url
	$urls = array();
	
	
	//generate url for all pages
	$pages = glob(getDataDir()."pages/*.metadata.php");	
	foreach ($pages as $page) {
		$id = basename($page,".metadata.php"); //recover id from filename
		$stats = stat(getDataDir()."pages/".$id.".php"); //recover stat from file
		$urls[$id]['loc'] = getWebRoot()."page.php?page=$id"; //generate url
		$urls[$id]['lastmod'] = Date::dater($stats['mtime'],"Y-m-d"); //add last date mod
	}
	
	//raise event in order to add url from external component
	EventManager::getInstance()->getEvent("sitemapEvent")->raise($urls);
	
	$res->setMimeType("text/xml");	
	$xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
	$xml .= "<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
			 xsi:schemaLocation=\"http://www.sitemaps.org/schema/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\"
			 xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
	
	foreach ($urls as $url) {		
		$xml .= "<url>\n\t<loc>".Url::encode($url['loc'])."</loc>\n\t<lastmod>".$url['lastmod']."</lastmod>\n</url>\n";		
	}
	
	$xml .= "</urlset>";
	$res->setBody($xml);
	$res->send();