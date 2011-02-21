<?php if (!defined('CLYDEPHP')) die("Direct access not allowed") ;?>
<?php

class HttpResponse {

	private $_body;
	private $_headers = array();	

	public function send() {
			
		foreach ($this->_headers as $header) {
			header($header);
		}
			
		echo $this->_body;
			
	}
	
	public function sendCompressed($encoding="gzip") {
			
		$this->addHeader("Content-Encoding",$encoding)
			 ->compressBody()
			 ->send();
			
	}
	
	public function compressBody() {

		$this->_body = gzencode($this->_body, 9,FORCE_GZIP); 
		
		return $this;		
	}

	public function addHeader($name,$value) {
		
		$this->_headers[] = $name.": ".$value;
		
		return $this;
	}
	
	public function addRawHeader($header) {
		
		$this->_headers[] = $header;
		
		return $this;
	}

	public function setBody($body) {
		
		$this->_body = $body;
		
		return $this;
	}	
	
	public function getBody() {
		return $this->_body;		
	}	
	
	public function setStatus($status) {
		if (is_null($status) || !is_int($status)) {
			throw new ClydePhpException("Status must be an integer");
		}	
		
		$status_header = 'HTTP/1.1 ' . $status . ' ' . $this->getStatusCodeMessage($status);
		// set the status
		$this->addRawHeader($status_header);
		
		return $this;
		
	}
	
	public function setMimeType($mime) {
		if (is_null($mime) || !is_string($mime)) {
			throw new ClydePhpException("MimeType must be a not null string");
		}
		
		$this->addHeader("Content-Type",$mime);
		
		return $this;
	}
	
	private function getStatusCodeMessage($status)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}

}


?>