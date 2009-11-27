<?php

/**
 * Copyright 2008-2010 Michelle Steigerwalt <msteigerwalt.com>.
 * Part of RabidCore.
 * For licensing and information, visit <http://rabidcore.com>.
 */
class DataModel extends Base {

	private static $refs = Array();

	protected $fields    = Array(); //The fields of the table associated with this model.
	protected $required  = Array(); //No field in the required array may be null before save.
	protected $autosave  = true;    //If set to true, the object will always save onunload.
	protected $keyField  = 'id';    //Set to false if you don't want a primary key field.
	//protected $table   = null;    //Set a table if the table name differs from the model name. 

	/* Don't touch $__ values unless you want to do something fancy. */
	private $__raw       = Array(); //The raw data that came out of the database.
	private $__modified  = Array(); //The raw data to go back into the database.
	private $__new       = true;    //Does this object need to be INSERTed?
	private $__linked    = Array(); //Query calls for linked objects.
	private $__rules     = Array(); //Rules to enforce on field values.
	private $complaints  = Array(); //User-level errors thrown by validation methods.

	/**
	 * The model constructor takes an optional data array which will be the same
	 * creating a new object and using -> to set each key of the array.
	 */
	public function __construct($data = null, $new = true) {
		$this->__new = $new;
		foreach($this->fields as $field) $this->__raw[$field] = null; 
		if (is_array($data) && $new == 1) $this->create($data); 
		else if ($new == 0) $this->fillRaw($data);
		if ($this->autosave == 1)  register_shutdown_function(Array(&$this, "save"));
		/* This line down here is quick hack to mitigate a scary recursion problem
		   for self-referencial links. */
		if ($data != "reference") $this->init();
	}

	public function hasComplaints($field=null) {
		if ($field) return (count($this->complaints[$field]) > 0);
		return (count($this->complaints) > 0);
	}

	public function getComplaints($field=null) { 
		if ($field) return $this->complaints[$field];
		return $this->complaints;
	}

	public function addComplaint($field, $message) {
		if (!is_array($this->complaints[$field])) {
			$this->complaints[$field] = Array();
		}
		$this->complaints[$field][] = $message;
	}

	/**
	 * Resets all complaints.  If autosave is enabled, this will force the
	 * object to save.
	 *
	 * Only use this method if you know what you're doing.
	 *
	 * THIS WILL ALLOW BAD DATA TO BE SAVED TO THE DATABASE.
	 */
	public function clearComplaints($key=null) {
		if ($key) unset($this->complaints[$key]); 
		else $this->complaints = Array();
	}

	public function toArray() {
		$return = Array();
		foreach ($this->__raw as $key=>$value) {
			$return[$key] = $this->$key;
		} return $return;
	}

	/**
	 * Returns the table name of this model.  This is a get method because if the
	 * table name isn't explicitly set, the model name will be used instead.
	 */
	public function getTable() {
		if ($this->table) return $this->table;
		return $this->model;
	}

	/**
	 * Returns the name of the model (which is the class name lowercased).
	 */
	public function getModel() {
		if ($this->modelName) return $this->modelName;
		return strtolower(get_class($this));
	}

	/**
	 * Returns the primary key value of the data object.
	 */
	public function getKey() {
		$field = $this->keyField;
		return $this->$field;
	}

	public function getKeyField() {
		return $this->keyField;
	}

	/**
	 * Creates a link between this model and another model.  Normally, this 
	 * method is as simple as:
	 *
	 *     $this->linkOne("position"); 
	 *
	 * @param model      (string) The name of the model to link.
	 * @param key        (string) The key name on this Model to access data.
	 * @param foreignKey (string) The foreign key of the model.
	 * @param refKey     (string) The key the foreign key refers to.
	 */
	public function linkOne($model, $key = null, $fKey = null, $refKey = null) {
		$linked      = self::getRef($model);
		$myTable     = $this->table;
		$key         = pick($key, $model);
		$fKey        = pick($fKey, $model."_id");
		$refKey      = pick($refKey, $linked->keyField);
		//There's no link made if the foreign key = 0 (NULL).
		if ($this->$fKey == 0) return 0;
		$this->__linked[$key] = new Query($model, Array($refKey=>$this->$fKey), 1);
	}

	/**
	 * Creates a link between this model and another model.  Normally, this 
	 * method is as simple as:
	 *
	 *     $this->linkMany("comments"); 
	 *
	 * @param model      (string) The name of the model to link.
	 * @param key        (string) The key name on this Model to access data.
	 * @param fKey       (string) The foreign key of the model.
	 * @param refKey     (string) The key the foreign key refers to.
	 */
	public function linkMany($model, $key = null, $fKey = null, $refKey = null){
		$key        = pick($key, $model);
		$fKey       = pick($fKey, $this->model."_id");
		$refKey     = pick($refKey, $this->keyField);
		$this->__linked[$key] = new Query($model, Array($fKey=>$this->refKey));
	}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Scope functions determine if a user can edit, view, delete, or create 
 * objects.  Edit and view are subsectioned into keys. 
 *
 * For data validation, see the validate method.
 *+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 */

	/**
	 * If this method returns true, the edit on this object/key will take place.
	 */
	public function canEdit($key = null) { return true; }

	/**
	 * If this method returns true, this object's data will be open to view.
	 */
	public function canView($key = null) { return true; }

	/**
	 * If this method returns true, this object can be deleted.
	 */
	public function canDelete() { return true; }

	/**
	 * If this method returns true, a new object can be created.
	 */
	public function canCreate() { return true; }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Data Validation includes key-specific validation methods as well as generic
 * rule-specific validations.
 * 
 * See the validate method for information on key-specific validation and the 
 * enforce/addRule methods for information on generic validation.
 *+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 */

	/**
	 * Data validation can be done by creating a validateKey method, where 
	 * key is the name of the property to validate.
	 *
	 * For generic rules, use addRule($rule, $key1[, $key2...]);
	 */
	private function validate($key, $value) {
		if (method_exists($this, "validate$key")) {
			return call_user_func_array(Array($this, "validate$key"), $value);
		}
		foreach ($this->__rules as $r) {
			if (array_key_exists($key, $r)) return enforce($rule, $value);
		}
	}

	/**
	 * This is a dynamic arguments method.  The first argument is the rule to be
	 * enforced.  The remaining arguments are the keys to follow the passed rule.
	 *
	 * Example:
	 *     > $this->addRule('required', 'login', 'email', 'password');
	 */
	protected function addRule() {
		$args = func_get_args();
		$rule = array_shift($args);
		$keys = $args;
		$this->__rules[$rule] = $keys;
	}

	protected function enforce($rule, $value) {
		if (method_exists($this, "enforce$rule")) {
			return call_user_func_array(Array($this, "enforce$rule"), $value);
		}
	}

	/** 
	 * Feel free to add more rules.  This one is provided as an example.
	 */
	protected function enforceRequired($value) {
		if ($value == null) complain("is a required value");
	}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * CRUD stuff.  Except that most of the R is in the Query class.
 *+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 */

	/**
	 * Magic __set method.
	 * Redirected to modifyField for better readability.
	 */
	public function __set($key, $value) {
		$this->modifyField($key, $value);
	}

	/**
	 * Magic __get method.
	 * First, we check to see if there is a get method available.
	 * If not, we'll attempt to return the raw data.
	 * If there's no raw data, we'll just return null.
	 */
	public function __get($key) {
		$meth = "get$key";
		if ($this->retrieve($key)) {
			return $this->retrieve($key);
		} else if (method_exists($this, $meth)) {
			return $this->store($key, $this->$meth());
		} else if (isset($this->__linked[$key])) {
			return $this->getLinked($key);
		} 
		return $this->get($key);
	}

	public function get($key) {
		if ($this->canView($key)) return $this->getRaw($key);
	}

	/* Creates a new model object. */
	private function create($data) {
		if (!$this->canCreate()) throw new PermissionException();
		foreach ($data as $field=>$value) $this->$field = $value;
	}
	
	/**
	 * Saves the Model's __raw data to the database.  This method will be 
	 * called before the object is destroyed if $this->autosave is set to true 
	 * (default).
	 */
	public function save() {
		//Check all the required fields.
		if ($this->__rules['required']) {
			foreach ($this->__rules['required'] as $field) {
				$val = $this->$field;
				if ($val===null) $this->addComplaint($field, "required");
			}	
		}
		//Don't save if we don't have to: if the object hasn't been modified.
		if (count($this->__modified) == 0) {
			return false;
		}
		//Don't save if the object contains errors.
		if (count($this->complaints) > 0)  return false;
		//Are we creating a new file?
		if ($this->__new == true) {
			if ($this->canCreate()===false) throw new PermissionException();
			$this->__new = false;
			if (!$this->db) throw new Exception("Database not loaded.");
			$id = $this->db->insert($this->table, $this->__modified);
			if (!$id) throw new DataException("No ID granted to new item.");
			$this->fillRaw($this->__modified); $this->__modified = Array();
			$this->__raw['id'] = $id;
			return $this->id;
		//Has this object been modified?
		} else if ($this->keyField && count($this->__modified) > 0) {
			$this->db->update($this->table, $this->__modified, 
			                  Array($this->keyField=>$this->key));
			$this->fillRaw($this->__modified); $this->__modified = Array();
			return $this->id;
		} return false;
	}

	public function delete() {
		if (!$this->canDelete()) throw new PermissionException();
		$this->db->delete($this->table,Array($this->keyField=>$this->key));
	}

	/**
	 * Gets the raw data for a field.
	 */
	public function getRaw($key) {
		if (array_key_exists($key, $this->__modified)) {
			return $this->__modified[$key];
		} else if (array_key_exists($key, $this->__raw)) {
			return $this->__raw[$key];
		} else return null;
	}

	/**
	 * Returns a string representing a Database query to retrieve the object
	 * object(s) associated with this object.
	 *
	 * In The Future, this can be replaced with some super fancy JOIN method,
	 * assuming the current way of doing things causes horrible problems down
	 * the line.
	 */
	private function getLinked($key) {
		if ($this->__linked[$key]->limit == 1) { 
			return $this->__linked[$key]->getRow(0);
		} return $this->__linked[$key];
	}

	/**
	 * Fills the raw data of a field.  This should be done upon instanciation
	 * of an object.
	 */
	private function fillRaw($data) {
		foreach ($data as $field=>$value) {
			$this->__raw[$field] = $value;
		} return $this;
	}

	/**
	 * Will modify model data, assuming the data is listed in the __raw array.
	 * If the __raw data is the same as the passed value, no change will be
	 * made.  Otherwise, the change will show up in the __modified array, and
	 * will be sent via an UPDATE query to the database on save.
	 */
	private function modifyField($key, $value) {
		try {
			if ($this->canEdit($key) === false 
			    OR ($this->canCreate() === false AND $this->__new)) {
				$this->addComplaint($key, "is not editable");
				return false;
			}
			$this->validate($key, $value);
			if (count($this->fields) > 0 && !in_array($key, $this->fields)) {
				throw new DataException("Can't find field for $key in $this->model");
				return false;
			}
			if (method_exists($this, "set$key")) {
				$value = call_user_func(Array($this, "set$key"), $value);
				if ($value === false) return false;
			}
		} catch (UserDataException $e) { 
			$this->addComplaint($key, $e->getMessage());
			return false;
		}
		//If this Model has its fields declared and the key isn't in __raw:
		if (count($this->fields) > 0 && !isset($key, $this->__raw)) { 
			throw new DataException("$this->table.$key does not exist, "
				."but is defined in $this->model's fields array.");
		}
		//Now that we know the field is valid, we can set it in model.
		//Later, it will be saved to the database.
		$this->setRaw($key, $value);
		//We have to reset the complaints in case invalid values have been
		//set for this field in the past.
		$this->clearComplaints($key);
	}

	/**
	 * If the field exists in the __raw array and its value is different than 
	 * the one already set, the key and value will be added to the __modified
	 * array.
	 *
	 * Note that this method will NEVER be called in a new model object.
	 */
	protected function setRaw($key, $value) {
		if ($this->__raw[$key] !== $value) { $this->__modified[$key] = $value; }
	}

	/**
	 * Generally, changing the ID of a data model would be a Bad Idea.  So we
	 * won't allow it.
	 */
	public function setId() {
		throw new DataException("Cannot change the ID of a model object.");
	}

	/**
	 * Returns a model reference (for linking and such).  If the optional $key 
	 * parameter is passed, the specified key on the object will be returned
	 * rather than the object itself.
	 *
	 * You should never have to use this.
	 */
	public function getRef($model, $key = null) {
		if (isset(self::$refs[$model])) return self::$refs[$model];
		$class  = ucfirst($model);
		$refObj = new $class('reference');
		self::$refs[$model] = $refObj;
		return ($key === null) ? $refObj : $refObj->$key;
	}

	/**
	 * Returns an array of the raw, unscoped values of the Model object.
	 * DO NOT USE THIS TO OUTPUT DATA TO THE USER.
	 */
	public function getArray() {
		if (count($this->__raw) == 0) return false;
		$arr = Array();
		foreach ($this->__raw as $key=>$value) {
			if (array_key_exists($key, $this->__modified)) {
				$arr[$key] = $this->__modified[$key];
			} else $arr[$key] = $this->__raw[$key];
		} return $arr;
	}

	/**
	 * This method should be overridden in child classes which need __construct
	 * actions.
	 */
	protected function init() { }

}

?>
