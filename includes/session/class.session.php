<?php

class Session {

	private static $me = null;
	private $_store = null;
	private $isStarted = false;

	public function attachSessionStore($obj) {
		if (!is_object($obj) || !$obj instanceof SessionStore) {
			throw new ClydePhpException("The store must be a valid SessionStore subclass");
		}
			
		$this->_store = $obj;

		session_set_save_handler(array($obj,"open"),
		array($obj,"close"),
		array($obj,"read"),
		array($obj,"write"),
		array($obj,"destroy"),
		array($obj,"gc")
		);
		
		return $this;
	}

	public static function getInstance() {
		if (is_null(self::$me)) {
			self::$me = new Session();
		}
		return self::$me;
	}

	public function start($sessionName="clydephp") {
			
		if (!$this->isStarted) {
			session_name($sessionName);
			session_start();
			$this->isStarted = true;
		}
			
		return $this;
	}

	public function getId() {
		return session_id();
	}

	public function getSessionName() {
		return session_name();
	}
	
	public function isStarted() {
		return $this->isStarted;
	}

	public function regenerateId() {
		session_regenerate_id();
		return $this;
	}

	public function setValue($key, $value) {
		$_SESSION[$key] = $value;
		
		return $this;
	}

	public function getValue($key) {
		if (array_key_exists($key,$_SESSION)) {
			return $_SESSION[$key];
		}
		return null;
	}
	
	public function unsetValue($key) {
		if (array_key_exists($key,$_SESSION)) {
			unset($_SESSION[$key]);
		}
		
		return $this;
	}

}

?>