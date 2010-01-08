<?php

/**
 * RabidCore Bootstrapper
 *
 * Contains a few utility functions (which will probably be moved before final 
 * release), and is in charge of loading the configuration and sending each
 * request to the Router.
 *
 * To make RabidCore work, simply route all requests to this file and keep all
 * source files within the same directory as this file or within child
 * directories of this file.
 *
 * Copyright 2008 Michelle Steigerwalt <msteigerwalt.com>.
 * Part of RabidCore.
 * For licensing and information, visit <http://rabidcore.com>.
 */

	loadConfig();

	/**
	 * The request router looks at the URI path, tries to load it from /assets,
	 * then tries to route the request through the Router if it's a model.
	 * If it's not a model, the PageEngine tries to render the template file.
	 */
	function routeRequest() {
		$path = getPath();
		if (!$path) return PageEngine::renderPage('index');
		if (File::find("assets/$path")) File::render("assets/$path");
		try {
			$router = new Router();
			return $router->route($path);
		} catch(ModelExistenceException $e) {
			return PageEngine::renderPage($path);
		}
	}

	/**
	 * Throws a UserDataException.  For use in validation methods.  You should
	 * use this function instead of throwing your own exception in case user
	 * error handling is changed.
	 */
	function complain($message) {
		throw new UserDataException($message);
	}

	function link_to($link,$txt=null) {
		return '<a href="'.getLink($link).'">'.pick($txt,$link)."</a>";
	}

	function getLink($link) {
		$base = getBaseURI();
		return "$base/$link";
	}

	function pick() {
		$args = func_get_args();
		foreach ($args as $arg) if ($arg) return $arg;
	}

	/**
	 * Checks to see if the specified model exists.
	 */
	function model_exists($model) {
		try {
			__autoload(ucfirst($model));
		} catch (ClassException $e) {
			return false;
		} return true;
	}

	/**
	 * Amazing Autoloader!
	 * This basically just goes through all the subfolders of the autoloader's dir
	 * and checks to see if there are any files with <ClassName>.php hanging out.
	 * This way there's no need to hold the autoloader's hand or spoonfeed it
	 * information.
	 * 
	 * Suitable for use in most applications. Might not be very optimized, though.
	 */
	function __autoload($class) { 
		$runDir = LOCAL_CONTEXT;
		if ($runDir == null) {
			throw new ClassException("No context specified!");
		} elseif ($class == NULL || preg_match('/(\W)+/', $class)) { 
			throw new ClassException("Not a valid class name: $class.");
		}
		
		$file = searchDir($class,dirname(__FILE__));
		if ($file) { 
			require_once($file); 
		} else { 
			if ($file) require_once($file);
			/* This bit of code automatically generates empty class files based
			   on the class name.  */
			$parents = Array("Commands", "Mapper", "Exception", "Query");
			foreach ($parents as $p) {
				if (preg_match('/'.$p.'$/', $class)) {
					eval ("class $class extends $p { }");
					return;
				}
			}
			//Create a new empty class so the script doesn't die.
			eval("class $class {}");
			throw new ClassException("Class $class doesn't exist or can't be found.");
		}	

	}

	/**
	 * This could go into the File class, but then how would Env be able to find
	 * File.php?
	 */
	function searchDir($class, $dirname, $recursive = true) {
		$dirname = $dirname.'/';
		$dir = dir($dirname);
		$classFile = $dirname.$class.".php";
		if (file_exists($classFile)) { return $classFile; }
		while (false !== ($cdir = $dir->read())) {
			if (!preg_match('/^\./', $cdir)) {
				$node = null;
				if(is_dir($dirname.$cdir) && $recursive == true) {
					$node = searchDir($class, $dirname.$cdir."/");
				} if ($node) { return $node; }
			}
		} $dir->close();
		return false;
	}

	function loadConfig($file = 'config.php') {
		if (!file_exists($file)) throw new ConfigException("Config not found.");
		include($file);
	}

	function getPath() {
		if (defined('__PATH__')) return __PATH__;
		$uri = dirname($_SERVER['SCRIPT_NAME']);
		$basePath = preg_replace("/\/(.*).php$/", '', $_SERVER['PHP_SELF']);
		$baseURI  = preg_replace("/\/$/", "", $uri);
		$rpath = explode("?", str_replace($uri."/", '', $_SERVER['REQUEST_URI']));
		if ($rpath[0] == "/") { $rpath[0] = "/index"; }
		define('__PATH__', $rpath[0]);
		return preg_replace('/^\//', '', $rpath[0]);
	}

	function setPath($path) {
		define('__PATH__', $path);
	}

	function getBaseURI() {
		return preg_replace("/\/[\w_]*.php$/", '', $_SERVER['PHP_SELF']);
	}

	function getFilePath() {
		return dirname(__FILE__);
	}

?>
