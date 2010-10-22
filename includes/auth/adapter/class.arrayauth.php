<?PHP

class ArrayAuth extends Auth
{
	 
	private $users;

	protected function __construct($params) {
		
		if (!isset($params['users']) || !is_array($params['users'])) {
			throw new ClydePhpException("You must pass a valid and non empty array of users");
		}
		$this->users = $params['users'];

		parent::__construct($params);
	}
	 
	// Assumes you have already checked for duplicate usernames
	public function changeUsername($new_username)
	{

		throw new ClydePhpException("Change username is not allowed");
		 
	}

	public function changePassword($new_password)
	{
		 
		throw new ClydePhpException("Change password is not allowed");
		 
	}

	// Login a user simply by passing in their username or id. Does
	// not check against a password. Useful for allowing an admin user
	// to temporarily login as a standard user for troubleshooting.
	// Takes an id or username
	public function impersonate($user_to_impersonate)
	{
		if (!array_key_exists($user_to_impersonate,$this->users)) {
			return false;
		}
		 
		$this->id       = $user_to_impersonate;
		$this->username = $user_to_impersonate;
		$this->level    = $this->users[$user_to_impersonate]['level'];

		if($this->useHashedPasswords === false)
			$pwd = $this->createHashedPassword($this->users[$user_to_impersonate]['password']);
		else
			$pwd = $this->users[$user_to_impersonate]['password'];

		$this->storeSessionData($this->username, $pwd);
		$this->loggedIn = true;

		return true;
	}
	 
	// The function that actually verifies an attempted login and
	// processes it if successful.
	// Takes a username and a *hashed* password
	protected function attemptLogin($un, $pw)
	{		

		if (!array_key_exists($un,$this->users)) {
			return false;
		}
		 
		if($this->useHashedPasswords === false) {
			$checkPw = $this->createHashedPassword($this->users[$un]['password']);
		}
		else
			$checkPw = $this->users[$un]['password'];
		 
		if($pw != $checkPw) return false;

		$this->id       = $un;
		$this->username = $un;
		$this->level    = $this->users[$un]['level'];

		$this->storeSessionData($un, $pw);
		$this->loggedIn = true;

		return true;
	}	
}
?>