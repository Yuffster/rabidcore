<?php

/**
 * A Singleton class which either creates or restores its session as needed 
 * on its first call.
 *
 * It's just a copy of Session base class with User methods tacked on, since
 * PHP 5.3 and below doesn't support late-static binding, making a DRY
 * ineheritable Singleton an impossibility.  The class may be segmented in
 * future versions of this application, assuming a suitable version of PHP
 * is available on the server.
 *
 * @author Michelle Steigerwalt <http://msteigerwalt.com>
 * @dependencies Base, User Model with hashPassword static method. 
 * @package RabidCore <http://rabidcore.com>
 * @copyright 2008 Michelle Steigerwalt 
 */
class CurrentUser extends Base {

	private static $instance;
	private $key = null;
	private $stored = Array();

	/** User-specific methods. **/
	public static function login($login, $pass) {
		$user = Query::findOne('user', Array('email'=>$login, 'password'=>User::hashPassword($pass)));
		if (!$user) return false;
		self::set('id', $user->id);
		return $user;
	}

	/**
	 * Close the session.
	 */
	public function logout() { session_destroy(); }

	public static function loggedIn() {
		return (self::getInstance()->get('loggedIn'));
	}

	public static function getId() {
		$t = self::getValue('id');
	}

	public static function getInstance() {
		if (!self::$instance) { self::$instance = new CurrentUser(); } 
		return self::$instance;
	}

	private function __construct() {
		$this->getSession();
		register_shutdown_function(array($this, "save"));
	}

	/**
	 * Store a session variable. 
	 */
	public static function set($key, $value) {
		$t = self::getInstance();
		$t->stored[$key] = $value;
	}

	/**
	 * Retrieve a session variable.
	 */
	public static function getValue($key) {
		$t = self::getInstance();
		if ($t->stored[$key]) return $t->stored[$key];
	}

	private function getSession() {
		$sid = $_COOKIE['widas_sid'];
		if (!$sid) { 
			session_start();
			$key = session_id();
			setcookie('widas_sid', $key);
		} else session_start($sid);
		$this->restore();
	}

	private function restore() {
		foreach ($_SESSION as $key=>$value) $this->stored[$key] = $value;
	}

	public function save() {
		$t = self::getInstance();
		foreach ($t->stored as $key => $value) $_SESSION[$key] = $value;
	}

}

?>
