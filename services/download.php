<?php
		require_once '../includes/master.inc.php';
		require_once '../cmsfunctions.php';
		
		$resp = new HttpResponse();
		$filename = HttpRequest::getHttpRequest()->getParam('file');		
		
		if(is_null($filename)) {
			$resp->setStatus(400)->setBody('No filename specified')->send();
		}
		
		//remove unwanted chars
		$filename=str_replace(array('../','./'),array('',''),$filename);
		
		//translate path
		$filename=getDataDir().'downloads/'.$filename;
		
		if(!file_exists($filename) || !is_readable($filename)) {
			$resp->setStatus(404)->setBody('File '.$filename.' not found')->send();
		} 				
		else {			
			$base = basename($filename);
			$statFile = $filename.'.stats';
			$count = 0;
			
			if(file_exists($statFile) || is_readable($statFile)) {
				$count = (int)file_get_contents($statFile);
			}	
			
			$count++;
			
			file_put_contents($statFile,$count,LOCK_EX);			
			
			$resp->addHeader('Cache-Control','must-revalidate, post-check=0, pre-check=0')
				->addHeader('Content-Disposition','attachment; filename='.$base)
				->addHeader('Content-Length', filesize($filename))
				->addHeader('Content-Type',File::mimeType($filename))
				->setBody(file_get_contents($filename))
				->send();
		}