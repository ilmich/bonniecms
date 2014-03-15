<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	if (!function_exists('mcrypt_module_open'))
		throw new ClydePhpException('MCrypt extension is missing!');
	
	class Cipher {
	
		private $mod;
	
		private function __construct() { }
	
		function __destruct() {
			mcrypt_generic_deinit($this->mod);
			mcrypt_module_close($this->mod);
		}
	
		public static function init($algo="rijndael-256",$mode="cbc") {
			$obj = new Cipher();
			$obj->mod = mcrypt_module_open($algo, "", $mode, "");
			if (!$obj->mod)
				throw new ClydePhpException('Unable to init cypher '.$algo.' with mode '.$mode);
	
			return $obj;
		}
	
		public function encrypt($text, $key) {
			$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->mod), MCRYPT_RAND);
			$keyCrypt = substr(sha1($key), 0, mcrypt_enc_get_key_size($this->mod));
	
			if (mcrypt_generic_init($this->mod, $keyCrypt, $iv) !==0 )
				throw new ClydePhpException('Unable to init cypher ');
	
			$encrypted = $iv.mcrypt_generic($this->mod, $text);
			return $encrypted;
		}
	
		public function decrypt($text, $key) {
			$iv = substr($text, 0,mcrypt_enc_get_iv_size($this->mod));
			$keyCrypt = substr(sha1($key), 0, mcrypt_enc_get_key_size($this->mod));
	
			if (mcrypt_generic_init($this->mod, $keyCrypt, $iv) !==0 )
				throw new ClydePhpException('Unable to init cypher ');
	
			$decrypted = mdecrypt_generic($this->mod, substr($text,mcrypt_enc_get_iv_size($this->mod)));
			return rtrim($decrypted,"\0");
		}
	}
