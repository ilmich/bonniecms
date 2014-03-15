<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }	

	class SmtpClient {
		
		private $host;
		private $port;
		private $sp; //socket stream pointer
		
		public function __construct($host, $port=25) {
			$this->host = $host;
			$this->port = $port;
		}

		public function connect() {			
			$this->sp = fsockopen($this->host, $this->port, $errno, $errmsg);
			if (!$this->sp)
				throw new SmtpException($errmsg);		

			$reply = $this->readReply();
			if ( !$reply['code'] === '220' )
				throw new SmtpException($reply['code']." ".$reply['msg']);
		}
		
		public function helo($clientHost=null) {			
			if (is_null($clientHost))
				$clientHost = gethostname();
			
			$reply = $this->sendCommand('EHLO '.$clientHost, true);			
		}
		
		public function mailFrom($from) {
			$reply = $this->sendCommand('MAIL FROM: <'.$from.'>');			
		}
		
		public function rcpt($rcpt) {
			$reply = $this->sendCommand('RCPT TO: <'.$rcpt.'>');
		}
		
		public function data($body) {
			$reply = $this->sendCommand("DATA");
			$reply = $this->sendBody($body);
		}
		
		public function quit() {
			$this->sendCommand("QUIT");
		}
		
		public function isConnected() {
			return is_resource($this->sp);
		}
		
		private function sendCommand($command, $multiLine = false) {
			if (!fputs($this->sp, $command."\r\n"))
				throw new SmtpException("I/O error, seems that server closes socket");
			
			$reply = $this->readReply($multiLine);
			if ( !($reply['code'] === '250' || $reply['code'] === '354' || $reply['code'] === '221') )
				throw new SmtpException($reply['code']." ".$reply['msg']);
		}
		
		private function sendBody($body) {
			if (!fputs($this->sp, $body."\r\n.\r\n"))
				throw new SmtpException("I/O error, seems that server closes socket");
				
			$reply = $this->readReply();
			if ( !($reply['code'] === '250' || $reply['code'] === '354' || $reply['code'] === '221') )
				throw new SmtpException($reply['code']." ".$reply['msg']);
		}
		
		private function readReply($multiLine = false) {
			$line = stream_get_line($this->sp, null, "\r\n");
			if ($line === "" )
				throw new SmtpException("I/O error, seems that server closes socket");
			
			if ($multiLine && substr($line, 3,1) === '-') {				
				do { 			
					$line = stream_get_line($this->sp, null, "\r\n");					
					if ($line === "" )
						throw new SmtpException("I/O error, seems that server closes socket");
				} while (substr($line, 3,1) !== ' ');
			}
			
			return $this->parseReply($line);
		}
		
		private function parseReply($line) {
			return array('code' => substr($line, 0,3), 'msg' => substr($line, 4));
		}
		
	}
	
	class SmtpException extends ClydePhpException {
		
	}