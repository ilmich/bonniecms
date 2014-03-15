<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

    abstract class SqlAdapter extends DbAdapter {
    	
    	protected $db; //database connection handler		        
        protected $queries;
        protected $result;
        protected $redirect = false;  	
    	
    	public function __construct($params) {
        	parent::__construct($params);
        	
            $this->db = false;
            $this->queries = array();
            if($this->getProperty("_connect") === true)
                $this->connect();
        }         	
                
        // Escapes a value and wraps it in single quotes.
        public function quote($var) {
            if(!$this->isConnected()) $this->connect();
            return "'" . $this->escape($var) . "'";
        }
        
        public function numQueries() {
            return count($this->queries);
        }

        public function lastQuery() {
            if($this->numQueries() > 0)
                return $this->queries[$this->numQueries() - 1];
            else
                return false;
        }
        
        // Takes nothing, a result, or a query string and returns
        // the correspsonding MySQL result resource or false if none available.
        public function resulter($arg = null) {
            if(is_null($arg) && is_resource($this->result))
                return $this->result;
            elseif(is_resource($arg))
                return $arg;
            elseif(is_string($arg)) {
                $this->query($arg);
                if(is_resource($this->result))
                    return $this->result;
                else
                    return false;
            }
            else
                return false;
        }
        
        public function constructTableName($name) {
        	if (is_null($this->getProperty("tablePrefix"))) 
        		return $name;
        	else
        		return $this->getProperty("tablePrefix").$name;	
        }
    }
    