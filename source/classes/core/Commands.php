<?php

/**
 * Copyright 2008 Michelle Steigerwalt <msteigerwalt.com>.
 * Part of RabidCore.
 * For licensing and information, visit <http://rabidcore.com>.
 */
class Commands extends Base {

	protected $defaultAction = 'index';
	//protected $model = null; //Set if not the same as model name + "Commands".
	protected $redirect = null;
	protected $errors   = Array();

	public function index() {
		return new Query($this->model);	
	}

	public function show($id) {
		if (!is_numeric($id)) throw new Exception("ID must be a number.");
		$this->onShow($item);
		return Query::findOne($this->model, $id);
	}

	public function edit($id, Array $data = null) {
		$item = Query::findOne($this->model, $id);
		if ($data) {
			foreach ($data as $key=>$value) $item->$key = $value;
			$this->errors = $item->errors;
			$this->setRedirect("show/$item->id", "Item edited.");
			$this->onEdit($item);
			$item->save();
		} return $item;
	}

	public function delete($id) {
		$item = Query::findOne($this->model, $id);
		$item->delete();
		$this->setRedirect('index', "Item deleted.");
		$this->onDelete($item);
		return $item;
	}

	public function create(Array $data = null) {
		$class = ucfirst($this->model);
		if ($data) {
			$obj = new $class();
			foreach ($data as $key=>$value) $obj->$key = $value;
			$this->errors = $obj->errors;
			$obj->save();
			$this->onCreate($obj);
			$id = Database::getLastId();
			$result = new Query($this->model, $id);
			$this->setRedirect("show/$id", ucfirst($this->model+" created."));
		} if ($result) return $result->getRow(1);
	}

	public function getModel() {
		if (isset($this->model)) return $this->model;
		return strtolower(preg_replace("/Commands$/", "", get_class($this)));
	}

	private function setRedirect($action, $message=null) {
		$this->redirect = "$this->model/$action";
	}

	public function getRedirect() { return $this->redirect; }

	public function getErrors()   { return $this->errors;   }

	protected function onCreate($obj) { }

	protected function onDelete($obj) { }

	protected function onShow($obj)   { }

	protected function onEdit($obj)   { }

}

?>
