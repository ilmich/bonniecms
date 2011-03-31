<?php 

	require_once 'includes/master.inc.php';		
	
	$req = Cms::getHttpRequest(); //get request	
	$resp = null; 
	
	if ($req->isGet()) { //component accept only get request
		$resp = Cms::getCachedHttpResponse(); //get cached response
		
		if (is_null($resp)) { //if respone not in cache start logic
			$resp = new HttpResponse('text/html');	//new empty response
			
			//get page id
			$pageId = String::slugify($req->getParam('page'));
				
			if ($pageId === '') {
				$pageId = 'home';
			}	
			
			$lang = Lang::getLocale(); // get current language
			//check if the page is avaible on filesystem
			if (is_readable(getDataDir().'pages/'.$pageId.'.metadata.php') || is_readable(getDataDir().'pages/'.$pageId.'.'.$lang.'.metadata.php')){		
				//check for localized version
				if (is_readable(getDataDir().'pages/'.$pageId.'.'.$lang.'.metadata.php')) {
					$page = require_once getDataDir().'pages/'.$pageId.'.'.$lang.'.metadata.php';
					$contentFile = getDataDir().'pages/'.$pageId.'.'.$lang.'.php';		
				}else { 
					$page = require_once getDataDir().'pages/'.$pageId.'.metadata.php';
					//set the filename of the page		
					$contentFile = getDataDir().'pages/'.$pageId.'.php';
				}		
			}else {
				//try to find page in database	
				$db = Database::getDatabase('pages');
				
				if (file_exists($db->table(true))) {
					if ($row = $db->getRow(array($pageId.'.'.$lang,$pageId),false)) {
						//shift first result in order to show the translated page, if exists				
						$page = array_shift($row);				
					}
				}
				
				if (!isset($page)) { //no page found
					$page = array('showTitle' => true,
								  'title' => Lang::getMessage('PAGE_NOT_FOUND_TITLE'),
								  'content' => Lang::getMessage('PAGE_NOT_FOUND'),
								  'meta' => array('robots' => 'noindex'));	
					$resp->setStatus(404);		
				}	
			}				
			
			$template = getTemplateName(); //get configured template
				
			//load main template
			$tpl = loadTemplate('index.php',$template);
			if (is_null($tpl)) {		
				die ('Unable to load template for rendering');		
			}
			//add page meta
			if (isset($page['meta'])) {
				$tpl->addMetaHeaders($page['meta']);
			}
			$tpl->fromArray($page); //put page into template
			$tpl->pageId = $pageId;
			
			//if filesystem mode is activated, load and render the page
			if (isset($contentFile)) 
				$tpl->content=$tpl->renderFile($contentFile);
			
			//load and render the component template
			$tpl->mainBody = $tpl->renderFile(findTemplate('page.php',$template));	
			
			//launch the onRender event
			EventManager::getInstance()->getEvent('onRender')->raise($req,$tpl);		
	
			//minify or not minify
			if (getCmsConfig('MINIFY')) {
				$resp->setBody(minifyHtml($tpl->render()));
			}else {
				$resp->setBody($tpl->render());
			}
			//put response in cache
			Cms::setCachedHttpResponse($resp);
		}		
	}else {
		//create new response with error
		$resp = new HttpResponse();						
		$resp->setStatus(405)
			 ->setBody($resp->getStatusCodeMessage(405))
			 ->send();
		exit(-1);		
	}
	
	Cms::sendHttpResponse($resp);

