<?php
/**
 *
 * DataBase Class
 * =============================
 * Author : 양한빈
 * Date : 2021-01-13 17:00
 * Version : 1.0
 * Describe : DB이용시 절차 구조에서 클래스 구조로 바꾸고, 한 앱에서 다른 종류의 db를 이용하기 쉽도록 하기 위해 작성함.
 * History : 2020-01-13 최초작성
 *
 */

class db {
    protected $conn;
	protected $dbms;
	protected $query;
    protected $show_errors = true;
    protected $query_closed = true;
	public $query_count = 0;

	public function __construct($dbms = DBMS,$name = DB_INFO['NAME'],$server = DB_INFO['SERVER'],$user = DB_INFO['USER'],$password = DB_INFO['PASSWORD'],$charset = 'utf8') {
		$this->conn = $this->connect($dbms,$name,$server,$user,$password,$charset);
	}

	public function connect($dbms = DBMS,$name = DB_INFO['NAME'],$server = DB_INFO['SERVER'],$user = DB_INFO['USER'],$password = DB_INFO['PASSWORD'],$charset = 'utf8') {
		$conn = false;
		switch(strtolower($dbms)) {
			case 'mariadb':
			case 'mysql':
				if(class_exists('mysqli')) {
					$conn = new mysqli($server,$user,$password,$name);
					if ($conn->connect_error) {
						die('Database connection failed: ' . $conn->connect_error);
					}
					$conn->set_charset($charset);
				}
				break;

			case 'mongo':
			case 'mongodb':
				if(class_exists('Mongo')) {
					//$conn = new Mongo()->selectDB($name);
				}
				break;

			case 'mssql':
				if(function_exists('sqlsrv_connect')) {
					$conn = sqlsrv_connect($server,
						array(
							'Database'=>$name,
							'Uid'=>$user,
							'PWD'=>$pasword
						)
					);
				}
				break;

			case 'oracle':
				if(function_exists('oci_connect')) {
					$conn = oci_connect($user,$password,$server.'/'.$name,$charset);
				}
				break;
		}
		if($conn) {
			$this->dbms = strtolower($dbms);
		}

		return $conn;
	}
	public function close() {
		$this->conn->close();
	}

	public function query($query) {
		if (!$this->query_closed) {
			$this->query->close();
		}
		if ($this->query = $this->conn->prepare($query)) {
			if (func_num_args() > 1) {
				$x = func_get_args();
				$args = array_slice($x, 1);
				if(sizeof($args) == 1 && $args[0] == '') {
				}
				else {
					$types = '';
					$args_ref = array();
					foreach ($args as $k => &$arg) {
						if (is_array($args[$k])) {
							foreach ($args[$k] as $j => &$a) {
								$types .= $this->_gettype($args[$k][$j]);
								$args_ref[] = &$a;
							}
						} else {
							$types .= $this->_gettype($args[$k]);
							$args_ref[] = &$arg;
						}
					}
					array_unshift($args_ref, $types);
					call_user_func_array(array($this->query, 'bind_param'), $args_ref);
				}
			}
			$this->query->execute();
			if ($this->query->errno) {
				$this->error('failed to execute: ' . $this->query->error);
			}
			$this->query_closed = false;
			$this->query_count++;

		} else {
			echo $query;
			$this->error('query string error: ' . $this->conn->error);
		}
		return $this;
	}

	public function insert($table,$values,$where = null,$where_values = null) {
		if($where != null) {
			if($this->count($table,$where,$where_values) > 0) {
				return $this->update($table,$values,$where,$where_values);
			}
		}

		switch($this->dbms) {
			case 'oracle':
			case 'mysql':
			case 'mariadb':
			case 'mssql':
				if(is_array($values)) {
					foreach($values as $key => $val) {
						$fields[] = $key;
						$value_ref[] = '?';
						$value[] = $val;
					}
					$query = 'INSERT INTO '.$table.' ('.implode(',',$fields).') VALUES ('.implode(',',$value_ref).')';
				}
				else {
					$query = 'INSERT INTO '.$table.' '.$values;
				}
			break;

			case 'mongo':
			case 'mongodb':

			break;
		}

		return call_user_func_array(array($this,'query'),array_merge(array($query),$value));
	}

	public function update($table,$values,$where = null,$where_value = []) {
		switch($this->dbms) {
			case 'oracle':
			case 'mysql':
			case 'mariadb':
			case 'mssql':
				foreach($values as $key => $val) {
					$fields[] = $key.'=?';
					$value_ref[] = '?';
					$vals[] = $val;
				}
				$query = 'UPDATE '.$table.' SET '.implode(',',$fields).((is_string($where))?' WHERE '.$where:'');
			break;

			case 'mongo':
			case 'mongodb':

			break;
		}
		if(is_array($where_value) === FALSE) $where_value = array($where_value);

		return call_user_func_array(array($this,'query'),array_merge(array($query),array_merge($vals,$where_value)));
	}

	public function delete($table,$where,$vals) {
		switch($this->dbms) {
			case 'oracle':
			case 'mysql':
			case 'mariadb':
			case 'mssql':
				$query = 'DELETE FROM '.$table.' WHERE '.$where;
				if(is_array($vals)) $query = array_merge(array($query),$vals);
				else $query[] = $query;
			break;
			
			case 'mongo':
			case 'mongodb':

			break;
		}

		call_user_func_array(array($this,'query'),$query);

		return true;
	}

	public function get($table) {
		$where = null;
		$fields = '*';
		if (func_num_args() > 1) {
			$x = func_get_args();
			$args = array_slice($x, 1);
			$args_ref = array();
			foreach ($args as $k => &$arg) {
				if (is_array($args[$k])) {
					foreach ($args[$k] as $j => &$a) {
						//$types .= $this->_gettype($args[$k][$j]);
						$args_ref[] = &$a;
					}
				} else {
					if($where == null) $where = $args[$k];
					else $fields = $args[$k];
				}
			}
		}
		$query = 'SELECT '.$fields.' FROM '.$table;
		if(is_string($where)) $query .= ' WHERE '.$where;
		if(sizeof($args_ref) > 0) $query = array_merge(array($query),$args_ref);
		else $query = array($query);

		call_user_func_array(array($this,'query'),$query);
		
		return $this->fetch();
	}

	public function count($table,$where = '',$vals = null,$fields = null) {
		if($fields == null) $fields = '*';
		elseif(is_array($fields) == TRUE) $fields = implode(',',$fields);
		$query = ' SELECT COUNT('.$fields.') AS CNT FROM '.$table;
		if(is_string($where) && $where != '') $query .= ' WHERE '.$where;
		if(is_array($vals) && sizeof($vals) > 0) $query = array_merge(array($query),$vals);
		else $query = array($query);
		if(call_user_func_array(array($this,'query'),$query)) {
			$this->query->store_result();
			//return $this->rows();
			return $this->fetch()[0]['CNT'];
		}
		else {
			return -1;
		}
	}

	public function max($table,$field,$where = '',$vals = null) {
		$query = ' SELECT MAX('.$field.') AS MAX FROM '.$table;
		if(is_string($where) && $where != '') $query .= ' WHERE '.$where;
		if(is_array($vals) && sizeof($vals) > 0) $query = array_merge(array($query),$vals);
		else $query = array($query);
		if(call_user_func_array(array($this,'query'),$query)) {
			$this->query->store_result();
			return $this->rows();
		}
		else {
			return -1;
		}
	}

	public function real_escape_string($str) {
		return mysqli_real_escape_string($this->conn, $str);
	}

	public function fetch($callback = null) {
		$params = array();
		$row = array();
		$meta = $this->query->result_metadata();
		while ($field = $meta->fetch_field()) {
			$params[] = &$row[$field->name];
		}
		call_user_func_array(array($this->query, 'bind_result'), $params);
		$result = array();
		while ($this->query->fetch()) {
			$r = array();
			foreach ($row as $key => $val) {
				$r[$key] = $val;
			}
			if ($callback != null && is_callable($callback)) {
				$value = call_user_func($callback, $r);
				if ($value == 'break') break;
			} else {
				$result[] = $r;
			}
		}
		$this->query->close();
		$this->query_closed = true;
		return $result;
	}

	public function fetchArray() {
	    $params = array();
        $row = array();
	    $meta = $this->query->result_metadata();
	    while ($field = $meta->fetch_field()) {
	        $params[] = &$row[$field->name];
	    }
	    call_user_func_array(array($this->query, 'bind_result'), $params);
        $result = array();
		while ($this->query->fetch()) {
			foreach ($row as $key => $val) {
				$result[$key] = $val;
			}
		}
        $this->query->close();
        $this->query_closed = TRUE;
		return $result;
	}

    public function rows() {
		$this->query->store_result();
		return $this->query->num_rows;
	}

	public function affectedRows() {
		return $this->query->affected_rows;
	}

    public function last_id() {
    	return $this->conn->insert_id;
    }

    public function error($error) {
		if($this->show_errors) {
			exit($error);
		}
    }

	public function in_q($arr) {
		for($i=0;$i<sizeof($arr);$i++) {
			$a[] = '?';
		}
		return implode(',',$a);
	}

	private function _gettype($var) {
		if (is_string($var)) return 's';
		if (is_float($var)) return 'd';
		if (is_int($var)) return 'i';
		return 'b';
	}
	
	public function begin_transaction() {
    	return $this->conn->begin_transaction();
    }
	
	public function commit() {
    	return $this->conn->commit();
    }
    
    public function rollback() {
    	return $this->conn->rollback();
    }

    public function mysqli_affected_rows() {
        return mysqli_affected_rows($this->conn);
    }
}
?>