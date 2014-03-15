<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }
	
	class EventCallback {
		
		public $method;
		public $context;
	
		public function __construct($method, $context) {
			$this->method = $method;
			$this->context = $context;
		}
	}
	
	class Event {
		
		private $callbacks;
	
		public function __construct() {
			$this->callbacks = array();
		}
	
		private function get_callback($args) {
			return (count($args) < 2) ? new EventCallback($args[0], '') : new EventCallback($args[1], $args[0]);
		}
	
		public function subscribe() {
			$callback = $this->get_callback(func_get_args());
			if (!in_array($callback, $this->callbacks)) {
				$this->callbacks[] = $callback;
			}
			else throw new Exception($callback->method.' already subscribed to this event');
		}
	
		public function unsubscribe() {
			$callback = $this->get_callback(func_get_args());
			$key = array_search($callback, $this->callbacks);
			if (!($key === false)) {
				unset($this->callbacks[$key]);
			}
			else throw new Exception($callback->method.' not subscribed to this event');
		}
	
		public function raise() {
			foreach($this->callbacks as $callback) {
				if (method_exists($callback->context, $callback->method) || function_exists($callback->method)) {
					$params = func_get_args();
					$callback = (!empty($callback->context)) ? array($callback->context, $callback->method) : $callback->method;
					call_user_func_array($callback, $params);
				}
				else 
					throw new Exception($callback->method.' does not exist');
			}
		}
	}	
	
	class EventManager {
		 
		// Singleton object. Leave $me alone.
		private static $me;
	
		private $events;
	
		private function __construct() {
			$this->events = array();
		}
		 
		public static function getInstance() {
			if(is_null(self::$me))
				self::$me = new EventManager();
			return self::$me;
		}
	
		public function getEvent($name) {
			if (!isset($this->events[$name])) {
				$this->events[$name] = new Event();
			}
			return $this->events[$name];
		}	
	}
		