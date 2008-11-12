<?

/**
 * Copyright 2008 Michelle Steigerwalt <msteigerwalt.com>.
 * Part of RabidCore.
 * For licensing and information, visit <http://rabidcore.com>.
 */
class Router extends Base {

	/**
	 * Set the X_REQUEST header to request an output other than a full HTML page.
	 */
	public function route($path) {
		$this->args = explode('/', $path);
		$this->model = array_shift($this->args);
		$class = ucfirst($this->model)."Commands";
		$this->controller  = new $class();
		$this->command     = pick(array_shift($this->args), 'index');

		if ($_POST['returnFormat']) {
			$this->returnFormat = $_POST['returnFormat'];
			unset($_POST['returnFormat']);
		} $this->args[] = $_POST;
		
		try { $this->result = call_user_func_array(Array($this->controller, $this->command), $this->args); }
		catch (Exception $e) { $this->exception = $e; }

		$returnFormat = strtolower($_SERVER['HTTP_X_REQUEST']);
		$meth  = "output_$returnFormat";
		if (method_exists($this, $meth)) return $this->$meth();
		return $this->output_full();
	}

	/**
	 * The default response method, outputs a full page. 
	 */
	private function output_full() {
		if ($this->doRedirect()) {
			header("Location: http://$_SERVER[SERVER_NAME]" .getLink($this->controller->redirect));
			//Just in case something goes horribly wrong, we'll add this fallback:
			echo "You should have been redirected <a href=\"".getLink($this->controller->redirect)."\">here</a>.";
			return;
		} try {
			$out = TemplateEngine::renderPage("$this->model/$this->command", 
			       Array('result'=>$this->result, 'errors'=>$this->controller->errors));
		} catch (Exception $e) {
			$this->exception = $e;
		} if ($this->exception) {
			$out = TemplateEngine::renderPage("error", Array('result'=>$this->exception));
		} return $out;
	}

    /** 
	 * Outputs a JSON object.
	 */
	private function output_json() {
		if (is_object($this->result) && method_exists($this->result, 'toArray')) {
			$result = $this->result->toArray();
		} else $result = $this->result;
		if ($this->exception) {
			$success = 0;
			$result = $this->exception->getMessage();
		} else if (count($this->controller->errors) > 0) {
			$success = 0;
			$result = $this->controller->errors;
		} else {
			$success = 1;
			if ($this->returnFormat) {
				$r = Array();
				foreach ($this->returnFormat as $alias=>$field) {
					if (is_numeric($alias)) $alias = $field;
					$r[$alias] = $result[$field];
				} $result = $r;
			}
		} return json_encode(Array('success'=>$success, 'data'=>$result));
	}

	/**
	 * Outputs the command view without a layout.
	 */
	private function output_partial() {
		if ($this->doRedirect()) return Router::route($this->contoller->redirect);
		return TemplateEngine::renderPartial("$this->model/$this->command", 
		       Array('result'=>$this->result, 'errors'=>$this->controller->errors));
	}

	private function doRedirect() {
		if ($this->controller->redirect && count($this->controller->errors) == 0) {
			return true;
		} return false;
	}

}

?>
