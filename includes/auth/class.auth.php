<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?PHP

abstract class Auth
{
	// Singleton object. Leave $me alone.
	protected static $me;

	public $id;
	public $username;
	public $level;

	protected $loggedIn;
	protected $useHashedPasswords=false;
	protected $salt;

	// Call with no arguments to attempt to restore a previous logged in session
	// which then falls back to a guest user (which can then be logged in using
	// $this->login($un, $pw). Or pass a user_id to simply login that user. The
	// $seriously is just a safeguard to be certain you really do want to blindly
	// login a user. Set it to true.
	protected function __construct($params)
	{
		$this->id             = null;
		$this->username       = null;
		$this->level          = 'guest';
		$this->loggedIn       = false;
		
		if (isset($params['useHashedPasswords'])) {
			$this->useHashedPasswords = $params['useHashedPasswords'];
		}
		
		if (isset($params['salt'])) {
			$this->salt = $params['salt'];
		}
		

	}

	public function init() {
		$this->loggedIn = $this->attemptSessionLogin();
		return $this;
	}
	
	public static function getAuth() {
		return self::$me;		
	}
	
	/**
	 * Standard singleton
	 * @return Auth
	 */
	public static function factory($params=null)
	{
		
		if (!Session::getInstance()->isStarted()) {
			throw new ClydePhpException("Session is not started");
		}
		
		if (is_null($params) || !is_array($params) || empty($params)) {
			throw new ClydePhpException("You must pass a valid array of parameters");
		}
		 
		if (!isset($params['type'])) {
			throw new ClydePhpException("You must pass a non empty type");
		}
		 
		$adapterName = ucfirst($params['type']."Auth");
	
		return new $adapterName($params);		
	}
	
	public static function configure($params) {
		self::$me = self::factory($params);		
	}

	// You'll typically call this function when a user logs in using
	// a form. Pass in their username and password.
	// Takes a username and a *plain text* password
	public function login($un, $pw)
	{
		$pw = $this->createHashedPassword($pw);
		return $this->attemptLogin($un, $pw);
	}

	public function logout()
	{
		$this->id             = null;
		$this->username       = null;
		$this->level          = 'guest';
		$this->loggedIn       = false;

		Session::getInstance()->setValue('un','');
		Session::getInstance()->setValue('pw','');
	}

	// Assumes you have already checked for duplicate usernames
	abstract public function changeUsername($new_username);

	abstract public function changePassword($new_password);

	// Is a user logged in? This was broken out into its own function
	// in case extra logic is ever required beyond a simple bool value.
	public function loggedIn()
	{
		return $this->loggedIn;
	}

	// Helper function that redirects away from 'admin only' pages
	public function requireAdmin($url)
	{
		if(!$this->loggedIn() || $this->level != 'admin')
			HttpRequest::getHttpRequest()->redirect($url);
	}

	// Helper function that redirects away from 'member only' pages
	public function requireUser($url)
	{
		if(!$this->loggedIn())
			HttpRequest::getHttpRequest()->redirect($url);
	}

	// Login a user simply by passing in their username or id. Does
	// not check against a password. Useful for allowing an admin user
	// to temporarily login as a standard user for troubleshooting.
	// Takes an id or username
	abstract public function impersonate($user_to_impersonate);

	// Attempt to login using data stored in the current session
	protected function attemptSessionLogin()
	{
		$sess = Session::getInstance();
		if (!is_null($sess->getValue('un')) && !is_null($sess->getValue('pw'))) {
			return $this->attemptLogin($sess->getValue('un'), $sess->getValue('pw'));
		}
		return false;
	}

	// The function that actually verifies an attempted login and
	// processes it if successful.
	// Takes a username and a *hashed* password
	abstract protected function attemptLogin($un, $pw);

	// Takes a username and a *hashed* password
	protected function storeSessionData($un, $pw)
	{
		if(headers_sent()) return false;
		$sessionName = Session::getInstance()->getSessionName();
		
		Session::getInstance()->setValue('un',$un);
		Session::getInstance()->setValue('pw',$pw);
		return true;
	}

	protected function createHashedPassword($pw)
	{		
		return sha1($pw . $this->salt);
	}

	// Generates a strong password of default length 9 characters.
	// Contains at least one symbol and one number.
	// The available characters have been chosen for legibility reasons.
	// This prevents users from being confused by things like 'l' versus '1'
	// and 'O' versus '0', etc.
	public static function generateStrongPassword($length = 9)
	{
		$all = str_split('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$%&*');
		$symbols = str_split('!@#$%&*');
		$digits = str_split('23456789');

		$password = '';
		for($i = 0; $i < $length - 2; $i++)
		$password .= $all[array_rand($all)];

		$password .= $symbols[array_rand($symbols)];
		$password .= $digits[array_rand($digits)];

		return str_shuffle($password);
	}

}
?>