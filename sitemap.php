<?php

	require_once "includes/master.inc.php";
	
	$res = new HttpResponse("text/xml");
	//array of site url
	$urls = array();	
	
	//generate url for all pages
	$pages = glob(getDataDir()."pages/*.metadata.php");	
	foreach ($pages as $page) {
		$id = basename($page,".metadata.php"); //recover id from filename
		$stats = stat(getDataDir()."pages/".$id.".php"); //recover stat from file
		$urls['url'] = array('url' => array('loc' => getWebRoot()."page.php?page=$id",
											'lastmod' => Date::dater($stats['mtime'],"Y-m-d"))
							); //generate url		
	}
	
	//raise event in order to add url from external component
	EventManager::getInstance()->getEvent("sitemapEvent")->raise($urls);
	
	$xml = ArrayUtils::toXml($urls,"urlset");
	
	$res->setBody($xml);
	$res->send();