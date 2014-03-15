<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); } 

	abstract class DbObject extends UniqueIdObject {
		
		private $dbAdapter = null;
				
		public function __construct($id=null,$datasource="default") {
			$this->dbAdapter = Database::getDatabase($datasource);			
			parent::__construct($id);
		}
		
		public function getDbAdapter() {
			return $this->dbAdapter;			
		}		 
		
		public function setDbAdapter($dbAdapter) {			
			if (!is_object($dbAdapter) || !is_subclass_of($dbAdapter,"DbAdapter") ) {
				throw new DatabaseException("Database adapter not subclass of DbAdapter");
			}			
			$this->dbAdapter = $dbAdapter;
			return $this;
		}
		
		public function constructTableName($name) {			
        	if ($this->getDbAdapter()->getDatabaseType() !== Database::TYPE_SQL) {
        		return $name;
        	}        	
        	return $this->getDbAdapter()->constructTableName($name);
        }
	}
	