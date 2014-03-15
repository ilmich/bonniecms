<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }	

	abstract class Auth {
		
		// Singleton object. Leave $me alone.
		protected static $me;
	
		public $id;
		public $username;
		public $level;
	
		protected $loggedIn;
		protected $useHashedPasswords=false;
		protected $salt;
		protected $expires;
	
		// Call with no arguments to attempt to restore a previous logged in session
		// which then falls back to a guest user (which can then be logged in using
		// $this->login($un, $pw). Or pass a user_id to simply login that user. The
		// $seriously is just a safeguard to be certain you really do want to blindly
		// login a user. Set it to true.
		protected function __construct($params) {
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
			
			if (isset($params['expires'])) {
				if (is_numeric($params['expires']))
					$this->$expires = $params['$expires'];
				else 
					throw new ClydePhpException('Parameter expires must be a numeric');
			} else {
				$this->expires = 0;
			}
		}
	
		public function init() {
			$this->loggedIn = $this->attemptSessionLogin();
			return $this;
		}
		
		/**
		 * 
		 * @return Auth
		 */
		public static function getAuth() {
			return self::$me;		
		}
		
		/**
		 * Standard singleton
		 * @return Auth
		 */
		public static function factory($params=null) {			
			if (!Session::getInstance()->isStarted()) {
				throw new ClydePhpException('Session is not started');
			}
			
			if (is_null($params) || !is_array($params) || empty($params)) {
				throw new ClydePhpException('You must pass a valid array of parameters');
			}
			 
			if (!isset($params['type'])) {
				throw new ClydePhpException('You must pass a non empty type');
			}
			 
			$adapterName = ucfirst($params['type'].'Auth');
		
			return new $adapterName($params);		
		}
		
		public static function configure($params) {
			self::$me = self::factory($params);		
		}
	
		// You'll typically call this function when a user logs in using
		// a form. Pass in their username and password.
		// Takes a username and a *plain text* password
		public function login($un, $pw) {
			$pw = $this->createHashedPassword($pw);
			return $this->attemptLogin($un, $pw);
		}
		
		public function loginEncrypted($un, $pw) {
			return $this->attemptLogin($un, $pw);
		}
	
		public function logout() {
			$this->id             = null;
			$this->username       = null;
			$this->level          = 'guest';
			$this->loggedIn       = false;
	
			$session = Session::getInstance();
			$sessionName = $session->getSessionName();
			
			//destroy session
			$session->destroy();
			setrawcookie($sessionName."_tkn",'',0);
			setrawcookie($sessionName,'',0);			
			
		}
	
		// Assumes you have already checked for duplicate usernames
		abstract public function changeUsername($new_username);
	
		abstract public function changePassword($new_password);
	
		// Is a user logged in? This was broken out into its own function
		// in case extra logic is ever required beyond a simple bool value.
		public function loggedIn() {
			return $this->loggedIn;
		}
	
		// Helper function that redirects away from 'admin only' pages
		public function requireAdmin($url) {
			if(!$this->loggedIn() || $this->level != 'admin')
				HttpRequest::getHttpRequest()->redirect($url);
		}
	
		// Helper function that redirects away from 'member only' pages
		public function requireUser($url) {
			if(!$this->loggedIn())
				HttpRequest::getHttpRequest()->redirect($url);
		}
	
		// Login a user simply by passing in their username or id. Does
		// not check against a password. Useful for allowing an admin user
		// to temporarily login as a standard user for troubleshooting.
		// Takes an id or username
		abstract public function impersonate($user_to_impersonate);
	
		// Attempt to login using data stored in the current session
		protected function attemptSessionLogin() {
			$sess = Session::getInstance();			
			$sessionName = $sess->getSessionName();
			
			//get security token
			$token = HttpRequest::getHttpRequest()->getCookie($sessionName."_tkn");
			if (String::isNullOrEmpty($token))
				return false;
			
			//explode token
			list($uid, $expiry, $data, $check) = split("\|", $token ,4);
			//check if there is a valid session and that the check value returned from client is the same
			if (String::isNullOrEmpty($sess->getValue('check')))
				return false;
			//compute secret key
			$k = hash_hmac("sha1", $uid."|".$expiry, $this->salt);
			
			$cipher = Cipher::init();
			//decrypt hashed password						
			$pw = $cipher->decrypt(hex2bin($data), $k);
			//compute check						
			$computedCheck = hash_hmac("sha1", $uid."|".$expiry."|".$pw."|".$sess->getValue('check'), $k);			
			
			if ($check !== $computedCheck)
				return false;			
			
			return $this->validateLoginId($uid, $pw);			
		}
	
		// The function that actually verifies an attempted login and
		// processes it if successful.
		// Takes a username and a *hashed* password
		abstract protected function attemptLogin($un, $pw);
		
		abstract protected function validateLoginId($id, $pw);
	
		// Takes a username and a *hashed* password
		protected function storeSessionData($un, $pw) {
			if(headers_sent()) 
				return false;
			
			$session = Session::getInstance();			
			$sessionName = $session->getSessionName();			
			//compute secret key
			$k = hash_hmac("sha1", $this->id."|".$this->expires, $this->salt);
			//compute session key
			$skey = uniqid($un,true);
			//store session key
			$session->setValue('check', $skey);				
			
			$cipher = Cipher::init();
			//encrypt hashed password
			$data = $cipher->encrypt($pw, $k);
			//compute check
			$check = hash_hmac("sha1", $this->id."|".$this->expires."|".$pw."|".$skey, $k);
						
			//send token to client
			$token = $this->id."|".$this->expires."|".bin2hex($data)."|".$check;			
			setrawcookie($sessionName."_tkn",$token);
			
			return true;
		}
	
		protected function createHashedPassword($pw) {		
			return sha1($pw . $this->salt);
		}
	
		// Generates a strong password of default length 9 characters.
		// Contains at least one symbol and one number.
		// The available characters have been chosen for legibility reasons.
		// This prevents users from being confused by things like 'l' versus '1'
		// and 'O' versus '0', etc.
		public static function generateStrongPassword($length = 9) {
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
	