<?php

/**
 * Copyright 2008 Michelle Steigerwalt <msteigerwalt.com>.
 * Part of RabidCore.
 * For licensing and information, visit <http://rabidcore.com>.
 */
class File {

	private static $instance;
	private static $types = Array();

	/**
	 * Constructor holds the $types array, which is very important for gleaning
	 * mime type based on extension.  Modify it to suit your needs.
	 */
	private function __construct() {
		$types['image']       = Array('png', 'gif', 'jpeg', 'jpg'=>'jpeg');
		$types['text']        = Array('html', 'htm'=>'html', 'css', 'rtf', 'xml', 'xsl');
		$types['audio']       = Array('mp3', 'mp4', 'wav');
		$types['video']       = Array('mov'=>'quicktime', 'mpeg');
		$types['application'] = Array('js'=>'javascript', 'json'=>'javascript',
		                              'exe'=>'octet-stream', 'zip', 'tar', 'pdf',
								      'doc'=>'msword', 'swf'=>'x-shockwave-flash',
									  'sit'=>'stuffit', 'rar'=>'x-rar');
		$this->types = $types;
	}

	/**
	 * Initializes the Singleton instance.
	 */
	private static function init() {
		if (!isset(self::$instance)) self::$instance = new File();
		return self::$instance;
	}

	/**
	 * Gleans the mime type based on extension.  To make this more accurate,
	 * rewrite to utilize more advanced (non-standard) PHP modules, or modify
	 * the $types array in the constructor of this class.
	 */
	public static function getMimeType($filename) {
		$inst = self::init();
		if (!preg_match('/\./', $filename)) return 'text';
		$ext = strtolower(array_pop(explode('.', $filename)));
		foreach ($inst->types as $type=>$extensions) {
			if (isset($extensions[$ext])) return "$type/$extensions[$ext]";
			else if (in_array($ext, $extensions)) return "$type/$ext";
		} return "text/plain";
	} 

	/**
	 * Outputs a file with proper headers and then dies.  File location is
	 * relative to env.php.
	 */
	public static function render($loc) {
		if (!self::find($loc)) throw new Exception("File $file doesn't exist.");
		if (preg_match("/\.php$/", $loc)) {
			header("Content-Type: text/html; charset=utf-8");
			include($loc);
		} else {
			$type = self::getMimeType($loc);
			$content = file_get_contents($loc);
			header("Content-Type: $type; charset=utf-8");
			header("Content-Length: ".filesize($loc));
			echo $content;
		} die();
	}

	/**
	 * Returns true if a file exists.  Location is based on the directory path
	 * of env.php.
	 */
	public static function find($loc) {
		$path = getFilePath();
		if (file_exists($path."/$loc") && !is_dir($path."/$loc")) return true;
		return false;
	}
	
	/**
	 * Returns the contents of a file.  Location is relative to env.php.
	 */
	public static function getContents($loc) {
		$file = getFilePath()."/$loc";
		if (!file_exists($file)) {
			throw new FileNotFoundException("File not found: $loc");
		} return file_get_contents(getFilePath()."/$loc");
	}

	/**
	 * Collects all the files in a directory and puts them into a single
	 * returned string. Recursive.
	 *
	 * Directory path is relative to env.php.
	 */
	static function collectDir($dname) {
		$directory = getFilePath()."/$dname";
		$dir = dir($directory);
		$l = Array();
		while ($f = $dir->read()) {
			if (is_dir("$directory/$f")) {
				if (!preg_match("/^\./", $f)) $l[] = self::collectDir("$dname/$f");
			} else { $l[] = self::getContents("$dname/$f"); }
		} $dir->close();
		return join("\n\n", $l);
	}

	/**
	 * Returns the $types array.  Useful if you want to print out a table of
	 * supported mime types.
	 */
	public static function getRecognizedTypes() {
		$inst = self::init();
		return $inst->types;
	}

}

?>
