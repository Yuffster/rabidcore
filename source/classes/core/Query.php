<?php

/**
 * Copyright 2008 Michelle Steigerwalt <msteigerwalt.com>.
 * Part of RabidCore.
 * For licensing and information, visit <http://rabidcore.com>.
 */
class Query implements Iterator {

	public    $limit   = null;
	private   $total   = 0; 
	private   $current = 0;
	private   $rows    = null;
	private   $objs    = Array();
	private   $model   = '';
	private   $order   = null;

	/**
	 * The constructor method creates a new Query object which searches for 
	 * items of the specified data model meeting the specified criteria.  If
	 * no criteria is set, all items will be returned.
	 */
	public function __construct($criteria = null, $limit = null, $page = null) {
		$this->model    = preg_replace('/Query$/', '', get_class($this));
		if ($this->model == null) throw new QueryException("Model not given");
		$this->criteria = $criteria;
		$this->limit = $limit;
		$this->page  = $page;
		return $this;
	}

	public static function create($model, $criteria = null, $limit = null, $page = null) {
		$class = $model."Query";
		return new $class($criteria, $limit, $page);
	}

	public function limit($limit, $page=0) {
		$this->limit = $limit;
		$this->page  = $page;
	}

	public function getFirst() {
		return $this->getRow(0);
	}

	public function orderBy($field, $order) {
		$this->order = "ORDER BY $field $order";
		return $this;
	}

	private function find($model, $criteria, $limit = null, $order = null) {
		$ref = self::getRef($model);
		$table = $ref->table;
		//If a number is provided as the criteria, search for that ID.
		if (is_numeric($criteria)) { 
			$field = self::getRef($model)->keyField;
			$criteria = Array($field=>$criteria);
		} return Database::select($table, $criteria, $limit, $order);
	}

	private function getRef($model, $key = null) {
		return DataModel::getRef($model, $key); 
	}

	private function getObj($data) {
		$class = ucfirst($this->model);
		return new $class($data, 0);
	}

	private function ensureSearch() {
		if ($this->rows === null) {
			$this->rows = $this->find($this->model, $this->criteria, $this->limit, $this->order);
			$this->total = count($this->rows);
		}
	}

	public function getTotal() {
		$this->ensureSearch();
		return $this->total;
	}

	public function toArray() { 
		$this->ensureSearch();
		$result = Array();
		foreach ($this->rows as $n=>$val) {
			$result[] = $this->getRow($n)->toArray();
		} return $result;
	}

	/** Iterator Stuff - Not Very Exciting. **/

	public function getRow($n) {
		$this->ensureSearch(); //Perform the query when the first row is requested.
		if ($n >= $this->total || $n < 0) return null;
		if (isset($this->objs[$n])) return $this->objs[$n];
		if (isset($this->rows[$n])) $this->objs[$n] = $this->getObj($this->rows[$n]); 
		else return null;
		return $this->objs[$n];
	}

	public function rewind() { $this->current = 0; }

	public function valid() { return !is_null($this->current()); } 

	public function current() { return $this->getRow($this->current); }

	public function key()  { return $this->current; }

	public function next() {
		$obj = $this->getRow($this->current);
		if ($obj) $this->current++;
		return $obj;
	}

}

?>
