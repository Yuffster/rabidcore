<?php

/**
 * Copyright 2008 Michelle Steigerwalt <msteigerwalt.com>.
 * Part of RabidCore.
 * For licensing and information, visit <http://rabidcore.com>.
 */
class PageEngine extends Base {

	protected static $baseDir = 'views/'; //The base directory for template files.

	public static function outputHTML($templateFile, Array $vars = null) {
		echo self::renderPartial($templateFile, $vars);
	}

	/**
	 * Will take a content template file (ie, users/view) as its
	 * content, find the closest main.php template file (going up each 
	 * directory until it's found), and then replace the placeholder text of
	 * <!-- Main Content Goes Here --> with the actual content. 
	 *
	 * (NOTE: Placeholders not implemented fully at this time.)
	 */
	public static function renderPage($contentTemplate, Array $vars = null) {
		$mainPage = self::renderPartial('template', $vars);
		$content  = self::renderPartial($contentTemplate, $vars);
		preg_match_all('/<!-- (.*) Goes Here -->/', $mainPage, $placeholders);
		foreach ($placeholders[1] as $placeholder) {
			$replace = self::getPlaceholder($placeholder);
			str_replace("<!-- $placeholder Goes Here -->", $replace, $mainPage);
		} return str_replace('<!-- Main Content Goes Here -->', $content, $mainPage);
	}

	public static function renderPartial($templateFile, Array $vars = null) {
		if ($vars) { 
			foreach ($vars as $key=>$value) {
				global $$key;
				$$key = $value;
			}
		} 
		ob_start();
			eval('?>'.File::getContents(self::$baseDir."$templateFile.php", $vars));
			$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	private static function getPlaceholder($name) {
		return null;
		$name = ucwords($name);
		$name[0] = strtolower($name[0]);
		$file = str_replace(' ', '', $name);
		return self::renderPartial($file);
	}

}

?>
