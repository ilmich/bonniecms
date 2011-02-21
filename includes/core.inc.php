<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php


abstract class BaseClass {

	protected $_params = array();

	public function __construct($params = array()) {
			
		if (!is_array($params)) {
			throw new ClydePhpException("Passing non array params to ".get_class($this)." constructor is not allowed");
		}
			
		$this->_params = $params;
	}

	public function getProperty($name) {
		if (!isset($this->_params[$name])) {
			return null;
		}
		return $this->_params[$name];
	}

	public function setProperty($name,$value) {
		$this->_params[$name] = $value;
	}
}


abstract class UniqueIdObject extends DynaBean {

	public $id;

	public function __construct($id=null) {
		$this->id = $id;
	}

}

class ClydePhpException extends Exception {

}

function clydePhpExceptionHandler($exception){

	echo "<div style='font-family:courier;'>";
	echo "<u>".get_class($exception).": ".$exception->getMessage()."</u>";
	echo "<ul style=\"list-style-type:none\">";
	echo "<li> at (".$exception->getFile().":".$exception->getLine().")";
	foreach ($exception->getTrace() as $trace) {
		echo "<li> at ";
		if (isset($trace['class'])){
			echo $trace['class'].$trace['type'];
		}
		echo $trace['function'];
		if (isset($trace['file'])) {
			echo "(".$trace['file'].":".$trace['line'].")";
		}
		echo "</li>";
	}
	echo "</ul>";
	echo "</div>";

}



?>