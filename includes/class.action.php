<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php

class ActionException extends ClydePhpException {

}

abstract class Action extends BaseClass{

	public function __construct($params = array()) {
		parent::__construct($params);
	}

	protected function init() {
			
	}

	public function processRequest($req) {
		return $req;
	}

	public function execute($req) {

		$this->init();
			
		$methodName = $this->getMethodName($req);
			
		$req = $this->processRequest($req);
		$res = $this->$methodName($req);
		$res = $this->processResponse($res);
			
		return $res;
	}

	public function processResponse($res) {
		return $res;
	}

	public function __call($name, $arguments) {
		throw new ActionException("Unknown method ".$name." in ".get_class($this));
	}

	abstract public function getMethodName($req);

}

class ActionRunner extends BaseClass {

	public function __construct($params = array()) {
			
		parent::__construct($params);
			
		//run file action
		$actionName = ucfirst(basename($_SERVER["SCRIPT_FILENAME"],".php"))."Action";
			
		$action = new $actionName();
		$res = $action->execute(HttpRequest::getHttpRequest());
		$res->send();
	}

}


?>