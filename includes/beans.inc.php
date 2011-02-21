<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php

class BeanUtils {

	public static function toArray($obj) {
		return get_object_vars($obj);
	}

	public static function fromArray($obj,$arr) {
		if(!is_object($obj) || !is_array($arr)) {
			return null;
		}
			
		foreach (array_keys($arr) as $key) {
			$obj->$key = $arr[$key];
		}
			
		return $obj;
	}

}

class DynaBean {

	public $_properties = array();

	public function __get($name) {
		if(isset($this->_properties[$name])) {
			return $this->_properties[$name];
		}
		return null;
	}

	public function __set($name,$value) {
		$this->_properties[$name] = $value;
	}

	public function __isset($name) {
		return isset($this->_properties[$name]);
	}

	public function __unset($name) {
		unset($this->_properties[$name]);
	}

	public function getDynaProperties() {
		return $this->_properties;
	}

	public function toArray() {
			
		$data = array_merge($this->getDynaProperties(),BeanUtils::toArray($this));
		//remove '_properties' class member
		unset($data['_properties']);
		return $data;
			
	}

	public function fromArray($arr) {
			
		return BeanUtils::fromArray($this,$arr);
			
	}

}


?>