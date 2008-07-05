<?php

/**
 * Copyright 2008 Michelle Steigerwalt <msteigerwalt.com>.
 * Part of RabidCore.
 * For licensing and information, visit <http://rabidcore.com>.
 */
class Base {

	private $__store = Array();

	protected function __toString() {
		return "[".get_class($this)."]";
	}

	protected function get($key) { return $this->$key; }

	/**
	 * Generic __get functionality is to check the stored values, then the get
	 * method for the requested key.
	 */
	protected function __get($key) {
		if (isset($this->stored[$key])) return $this->stored[$key];
		else return $this->checkGets($key);
	}

	/**
	 * Checks to see if there's a get method for the passed key.  If so, returns
	 * that key.  This is a very useful shorthand for __get methods in extended
	 * classes.
	 */
	protected function checkGets($key) {
		if (method_exists($this, "get$key")) { 
			$meth = "get$key"; 
			return $this->$meth();
		} return false;
	}

	/**
	 * Store a variable for later (useful for things that might be requested 
	 * multiple times and have calculation overhead).
	 *
	 * Values will be stored in a special array to mitigate worries about 
	 * trampling an object's important properties.
	 * 
	 * Returns the value set to make possible code such as:
	 *      return $this->store($key, 'value');
	 */
	 protected function store($key, $value) {
		$this->__remembered[$key] = $value;
		return $value;
	}


	protected function retrieve($key) {
		if (isset($this->__stored[$key])) return $this->__stored[$key];
		return false;
	}

	/**
	 * While not generally useful all across the board, this little method
	 * reduces a lot of code overhead in the data-handling classes and I just
	 * don't feel like making a whole new class just for five lines of code.
	 */
	protected function getDb() {
		$db = Database::init();
		return $db;
	}

}

?>
