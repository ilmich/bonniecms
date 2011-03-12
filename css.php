<?php
		require_once "includes/master.inc.php";		
		require_once CLYDEPHP_VENDOR."minify/CSS.php";		

		$req = Cms::getCms()->getHttpRequest();
				
		$filename = $req->getParam("css");		
		
		if(is_null($filename)) {
			$resp->setStatus(400)->setBody("No filename specified")->send();
			exit(-1);
		}
		
		$resp = new HttpResponse("text/css");
		$conf = getCmsConfig(null,"css");
		
		if (!isset($conf[$filename])) {
			$resp->setStatus(400)->setBody("Css $filename not configured")->send();
			exit(-1);
		}
		
		if (!isset($conf[$filename]['files']) || empty($conf[$filename]['files'])) {
			$resp->setStatus(400)->setBody("No css files to load for style $filename")->send();
			exit(-1);
		}
			
		$css = '';
			
		foreach ($conf[$filename]['files'] as $file) {
			$css .= @file_get_contents($file);
		}						
		
		if (isset($conf['minify']) && $conf['minify'])
			$css = Minify_CSS::minify($css,$conf[$filename]);
				
		$resp->addHeader("Content-Length", strlen($css))				
			->setBody($css);
				
		Cms::getCms()->sendHttpResponse($resp);
				
		