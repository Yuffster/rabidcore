<?php

/**
 * Copyright 2008 Michelle Steigerwalt <msteigerwalt.com>.
 * Part of RabidCore.
 * For licensing and information, visit <http://rabidcore.com>.
 */
class Database {
	
	private static $instance;
	private $totalQueries = 0;
	private $host;
	private $user;
	private $password;
	private $database;
	private $connection;
	public  $lastQuery;

	/**
	 * Loads the Singleton instance of the Database class.  If the Database 
	 * class hasn't been initiated yet, it'll connect to the database.
	 */
	public static function init($host = null) {
		if (!isset(self::$instance)) {
			self::$instance = new Database(); 
		} return self::$instance;
	}

	public static function getLastId() {
		$self = self::init();
		return mysql_insert_id($self->connection);
	}

	/**
	 * Connects to the database or whines about how it couldn't.
	 */
	private function __construct() {
		$this->host       = pick($host, DB_HOST, 'localhost');
		$this->user       = pick($user, DB_USER);
		$this->password   = pick($password, DB_PASS); 
		$this->database   = pick($database, DB_DATA);
		$this->connection = mysql_connect($this->host, $this->user, 
		                                  $this->password);
		if (!mysql_select_db($this->database)) { 
			throw new Exception("Could not connect to database.");
		}
	}

	/**
	 * Sanitizes a string to prevent a SQL injection attack.
	 */
	private function scrub($string) {
		if(get_magic_quotes_gpc()) { $string = stripslashes($string); } 
		return mysql_real_escape_string($string);
	}

	/**
	 * Performs a query based on a SQL string and returns the result as an
	 * associative array.
	 */
	private function getData($query) {
		$result = $this->doQuery($query);
		if (!$result) return null;
		while ($row = mysql_fetch_assoc($result)) {
			$return[] = $row;
		} return $return;
	}

	/**
	 * An alias to select which limits the result to the first returned row.
	 */
	public function selectFirst($table, $criteria) {
		$t = self::init();
		$result = $t->select($table, $criteria, 1);
		return $result[0];
	}

	/**
	 * Performs a search of the database rows.  See generateFilter for more on how
	 * the criteria array will be processed.
	 */
	public function select($table, $criteria, $limit = null, $extra = null) {
		$t = self::init();
		$filter = $t->generateFilter($criteria);
		$limit  = (is_numeric($limit)) ? "LIMIT $limit" : "";
		$sql    = "SELECT * FROM $table WHERE $filter $limit $extra";
		$rows   = $t->getData($sql);
		return $rows;
	}

	/** 
	 * Generates a filter based on an array of keys and desired values.
	 *
	 * Unless $complex is set to true, the criteria array will be considered
	 * a key/value pair of equals values.
	 * 
	 * Passing ({'id':7,'email':'me@site.com'}, 0) will do an = search on each
	 *     key/value pair.
	 * Passing ({'like':{'firstName':'%bob%'},'not like':{'lastName':'%smith'}}, 1)
	 *     would create a complex filter. 
	 */
	public function generateFilter($criteria) {
		if (is_string($criteria)) return "1 AND $criteria";
		$return = "";
		if (is_numeric($criteria)) $criteria = Array('id' => $criteria);
		if ($criteria === null) return "1 ";
		foreach ($criteria as $condition=>$filters) {
			if (!is_array($filters)) { 
				$filters = Array($condition=>$filters);
				$condition = "=";
			} foreach ($filters as $field=>$value) {
				$value   = $this->scrub($value);
				$return .= " AND $field $condition '$value'";
			}
		} return "1".$return;
	}

	/**
	 * This can be used to insert data into a table based on an array of field
	 * names and values to pass.
	 */
	public function insert($table, $data) {
		foreach ($data as $key=>$value) {
			$fields[] = "`".$this->scrub($key)."`";
			$values[] = "'".$this->scrub($value)."'";
		} $fieldlist = join(',', $fields); 
		$values = join(',', $values);
		$sql = "INSERT INTO $table ($fieldlist) VALUES ($values)";
		$this->checkErrors();
		$this->doQuery($sql);
	}

	/**
	 * Creates an UPDATE statement based on a table name and an associative 
	 * array of the data to modify.  The default limit is one object, but 
	 * this can be overridden with the third optional parameter.
	 */
	public function update($table, Array $data, $criteria, $limit = 1) {
		$where = $this->generateFilter($criteria);
		foreach ($data as $key=>$value) {
			$sets[] = "`".$this->scrub($key)."` = '".$this->scrub($value)."'";
		} $sets = join(', ', $sets); 
		if (is_numeric($limit) && $limit > 0) $limit = "LIMIT $limit";
		else $limit = null;
		$sql = "UPDATE $table SET $sets WHERE $where $limit";	
		$this->doQuery($sql);
	}

	/**
	 * Creates an DELETE statement based on a table name and criteria.
	 * The default limit is one object, but this can be overridden with a third
	 * optional parameter.
	 */
	public function delete($table, $criteria, $limit = 1) {
		$where = $this->generateFilter($criteria);
		$sql = "DELETE FROM $table WHERE $where LIMIT $limit";	
		$this->doQuery($sql);
	}
	
	/**
	 * Will do a generic query and return the result.  This method doesn't need
	 * to be called on its own most times.
	 */
	private function doQuery($query) {
		if ($query == null) throw new DataException("Query can't be null.");
		$this->totalQueries++;
		$this->queries[] = $query;
		$result = mysql_query($query);
		$this->checkErrors($query);
		$this->lastQuery = $query;
		return $result;
	}

	/**
	 * Throws an exception on a database error.
	 */
	private function checkErrors($query=null) {
		$error = mysql_error();
		if ($error) throw new DatabaseException($error);
		return $error;
	}

	/**
	 * Close the connection.
	 */
	public function __destruct() {
		mysql_close($this->connection);
	}

}

?>
