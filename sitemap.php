<?php

	require_once 'includes/master.inc.php';
	
	$res = new HttpResponse('text/xml');
	//array of site url
	$urls = array();	
	
	//generate url for all pages
	$pages = glob(getDataDir().'pages/*.metadata.php');	
	foreach ($pages as $page) {                            
		$id = basename($page,'.metadata.php'); //recover id from filename
                if (strpos($id, '_') == 0) { continue; }
		$stats = stat(getDataDir().'pages/'.$id.'.php'); //recover stat from file
		$urls[] = array('loc' => getWebRoot().'page.php?page='.$id,
											'lastmod' => Date::dater($stats['mtime'],"Y-m-d")
							); //generate url		
	}		
        
	//raise event in order to add url from external component
	EventManager::getInstance()->getEvent('sitemapEvent')->raise($urls);
	
        $xml = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.'<urlset>'.PHP_EOL;
        foreach ($urls as $url) {
            $xml .= '    <url>'.PHP_EOL;
            $xml .= '        <loc>'.$url['loc'].'</loc>'.PHP_EOL;
            $xml .= '        <lastmod>'.$url['lastmod'].'</lastmod>'.PHP_EOL;
            $xml .= '    </url>'.PHP_EOL;
        }
        $xml .= '</urlset>';
	
	$res->setBody($xml);
	$res->send();
