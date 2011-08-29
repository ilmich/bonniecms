<?php

	require_once 'includes/master.inc.php';		
	require_once CLYDEPHP_VENDOR.'minify/CSS.php';		

	$req = Cms::getHttpRequest();//get request
	if ($req->isGet()) { //component accept only get request
		$resp = new HttpResponse('text/css'); //new empty response				
		$filename = String::slugify($req->getParam('css'));	//get css name	
		
		if($filename ==='') {
			$resp->setStatus(400)
				 ->setBody('No filename specified')
				 ->send();
			exit(-1);
		}		
				
		$conf = getCmsConfig(null,'css');
		
		$css = Cms::getCachedObject($filename,'css');		
		if (is_null($css)) {					
			if (!isset($conf[$filename])) {
				$resp->setStatus(400)
					 ->setBody('Css '.$filename.' not configured')
					 ->send();
				exit(-1);
			}
			
			if (!isset($conf[$filename]['files']) || empty($conf[$filename]['files'])) {
				$resp->setStatus(400)
					 ->setBody('No css files to load for style $filename')
					 ->send();
				exit(-1);
			}
			//load all css files	
			foreach ($conf[$filename]['files'] as $file) {
				$css .= @file_get_contents($file);
			}						
			//minify css
			if (isset($conf['minify']) && $conf['minify'])
				$css = minifyCss($css,$conf[$filename]);	

			Cms::putObjectInCache($filename, $css,'css');			
		}
		
		$resp->addHeader('Content-Length', strlen($css))
			 ->setEtag($cssEtag)				
			 ->setBody($css);
				
		Cms::sendHttpResponse($resp);				
	} else {
		//create new response with error
		$resp = new HttpResponse();						
		$resp->setStatus(405)
			 ->setBody($resp->getStatusCodeMessage(405))
			 ->send();
		exit(-1);		
	}
	