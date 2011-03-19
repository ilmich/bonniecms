<?php
		require_once 'includes/master.inc.php';		
		require_once CLYDEPHP_VENDOR.'minify/CSS.php';		

		$req = Cms::getCms()->getHttpRequest();
				
		$filename = String::slugify($req->getParam('css'));		
		
		$resp = new HttpResponse('text/css');
		if(is_null($filename)) {
			$resp->setStatus(400)->setBody('No filename specified')->send();
			exit(-1);
		}		
				
		$conf = getCmsConfig(null,'css');
		$ch = Cms::getCms()->getCacheManager();
		$css = null;
		if ($ch) {
			$css = $ch->get($filename,'css');	
		}		
	
		if (is_null($css)) {					
			if (!isset($conf[$filename])) {
				$resp->setStatus(400)->setBody('Css $filename not configured')->send();
				exit(-1);
			}
			
			if (!isset($conf[$filename]['files']) || empty($conf[$filename]['files'])) {
				$resp->setStatus(400)->setBody('No css files to load for style $filename')->send();
				exit(-1);
			}
				
			$css = '';
				
			foreach ($conf[$filename]['files'] as $file) {
				$css .= @file_get_contents($file);
			}						
			
			if (isset($conf['minify']) && $conf['minify'])
				$css = minifyCss($css,$conf[$filename]);

			if ($ch) {				
				$ch->put($filename,$css,'css');
			}
			
		}				
		
		$resp->addHeader('Content-Length', strlen($css))				
				->setBody($css);
				
		Cms::getCms()->sendHttpResponse($resp);				
		