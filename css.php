<?php

	require_once 'includes/master.inc.php';		
	require_once CLYDEPHP_VENDOR.'minify/CSS.php';
	require_once CLYDEPHP_VENDOR.'minify/Minify/CSS/UriRewriter.php';		

	$req = Cms::getHttpRequest();//get request
	$resource = null; //empty css object
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
		
		$resource = Cms::getCachedObject($filename,'css');		
		if (is_null($resource)) {					
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
			$resource = new CssResource();
			$css = "";
			//load all css files	
			foreach ($conf[$filename]['files'] as $file) {
				$css .= @file_get_contents($file);
			}						
			
			//fix css url
			if (isset($conf[$filename]['prependRelativePath']))
				$css = Minify_CSS_UriRewriter::prepend($css, $conf[$filename]['prependRelativePath']);
			//minify css			
			if (isset($conf['minify']) && $conf['minify']) {
				var_dump($conf[$filename]);	
				$resource->setBody(minifyCss($css));
			}				
			else {
				$resource->setBody($css);
			}	
			$resource->generateEtag();
			
			Cms::putObjectInCache($filename, $resource,'css');			
		} 
		if (isset($conf['etag']) && $conf['etag']) {
			if ($resource->getEtag() === $req->getEtag()) {			
				$resp->setStatus(304)->send();
				exit;
			}
			$resp->setEtag($resource->getEtag());
		}		
		
		$resp->addHeader('Content-Length', $resource->getBodyLenght())			 			
			 ->setBody($resource->getBody());
				
		Cms::sendHttpResponse($resp);				
	} else {
		//create new response with error
		$resp = new HttpResponse();						
		$resp->setStatus(405)
			 ->setBody($resp->getStatusCodeMessage(405))
			 ->send();
		exit(-1);		
	}
	
	class CssResource {
		
		public $_body = '';
		public $_etag = '';		
		
		public function getBody() { 
			return String::nullToEmpty($this->_body); 
		}
		
		public function getEtag() { 
			return String::nullToEmpty($this->_etag); 
		}		
		
		public function setBody($body) {
			$this->_body = $body;
		}
		
		public function generateEtag() {
			$this->_etag = md5($this->_body);
			return $this->_etag;
		}
		
		public function getBodyLenght() {
			return strlen($this->_body);
		}
	}
	