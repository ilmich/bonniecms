<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

    class SqlObject extends DBObject {
        protected $tableName;
        protected $idColumnName;

        protected $columns = array();
        protected $className;

        public function __construct($dbName, $table_name, $columns, $id = null) {
        	parent::__construct($id,$dbName);        	
            $this->className    = get_class($this);
            $this->tableName    = $this->constructTableName($table_name);
            // A note on hardcoding $this->idColumnName = 'id'...
            // In three years working with this framework, I've used
            // a different id name exactly once - so I've decided to
            // drop the option from the constructor. You can overload
            // the constructor yourself if you have the need.
            $this->idColumnName = 'id';

            foreach($columns as $col)
                $this->columns[$col] = null;

            if(!is_null($id))
                $this->select($id);
        }

        public function __get($key) {
            if(array_key_exists($key, $this->columns))
                return $this->columns[$key];

            if((substr($key, 0, 2) == '__') && array_key_exists(substr($key, 2), $this->columns))
                return htmlspecialchars($this->columns[substr($key, 2)]);

            $trace = debug_backtrace();
            trigger_error("Undefined property via SqlObject::__get(): $key in {$trace[0]['file']} on line {$trace[0]['line']}", E_USER_NOTICE);
            return null;
        }

        public function __set($key, $value) {
            if(array_key_exists($key, $this->columns))
                $this->columns[$key] = $value;

            return $value; // Seriously.
        }

        public function select($id, $column = null) {
            $db = $this->getDbAdapter();

            if(is_null($column)) 
            	$column = $this->idColumnName;
            	
            $column = $db->escape($column);

            $db->query("SELECT * FROM `{$this->tableName}` WHERE `$column` = :id LIMIT 1", array('id' => $id));
            if($db->hasRows()) {
                $row = $db->getRow();
                $this->load($row);
                return true;
            }

            return false;
        }

        public function ok() {
            return !is_null($this->id);
        }

        public function save() {
            if(is_null($this->id))
                $this->insert();
            else
                $this->update();
            return $this->id;
        }

        public function insert($cmd = 'INSERT INTO') {
            $db = $this->getDbAdapter();

            if(count($this->columns) == 0) return false;

            $data = array();
            foreach($this->columns as $k => $v)
                if(!is_null($v))
                    $data[$k] = $db->quote($v);

            $columns = '`' . implode('`, `', array_keys($data)) . '`';
            $values = implode(',', $data);

            $db->query("$cmd `{$this->tableName}` ($columns) VALUES ($values)");
            $this->id = $db->insertId();
            return $this->id;
        }

        public function replace() {
            return $this->delete() && $this->insert();
        }

        public function update() {
            if(is_null($this->id)) return false;

            $db = $this->getDbAdapter();

            if(count($this->columns) == 0) return;

            $sql = "UPDATE {$this->tableName} SET ";
            foreach($this->columns as $k => $v)
                $sql .= "`$k`=" . $db->quote($v) . ',';
            $sql[strlen($sql) - 1] = ' ';

            $sql .= "WHERE `{$this->idColumnName}` = " . $db->quote($this->id);
            $db->query($sql);

            return $db->affectedRows();
        }

        public function delete() {
            if(is_null($this->id)) return false;
            $db = $this->getDbAdapter();
            $db->query("DELETE FROM `{$this->tableName}` WHERE `{$this->idColumnName}` = :id LIMIT 1", array('id' => $this->id));
            return $db->affectedRows();
        }

        public function load($row) {
            foreach($row as $k => $v)
            {
                if($k == $this->idColumnName)
                    $this->id = $v;
                elseif(array_key_exists($k, $this->columns))
                    $this->columns[$k] = $v;
            }
        }

        // Grabs a large block of instantiated $class_name objects from the database using only one query.
        public static function glob($class_name, $sql = null, $extra_columns = array()) {
            // Make sure the class exists before we instantiate it...
            if(!class_exists($class_name))
                return false;

            $tmp_obj = new $class_name;

            // Also, it needs to be a subclass of DBObject...
            if(!is_subclass_of($tmp_obj, 'SqlObject'))
                return false;
                
            $db = $this->getDbAdapter();

            if(is_null($sql))
                $sql = "SELECT * FROM `{$tmp_obj->tableName}`";

            $objs = array();
            $rows = $db->getRows($sql);
            foreach($rows as $row)
            {
                $o = new $class_name;
                $o->load($row);
                $objs[$o->id] = $o;

                foreach($extra_columns as $c)
                {
                    $o->addColumn($c);
                    $o->$c = isset($row[$c]) ? $row[$c] : null;
                }
            }
            return $objs;
        }

        public function addColumn($key, $val = null) {
            if(!in_array($key, array_keys($this->columns)))
                $this->columns[$key] = $val;
        }               
    }
    