<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }
	
	class HtmlTemplate extends Template {
		
		private $_metaHeaders = array();
		
		public function __construct($template) {			
			parent::__construct($template);			
			$this->metaHeaders = '';
			//set current url
			$this->currentUrl = Url::fullUrl();
		}
		
		public function addMetaHeader($key,$value) {
			$this->_metaHeaders[$key] = $value;
		}
		
		public function addMetaHeaders($metas) {
			$this->_metaHeaders = array_merge($this->_metaHeaders,$metas);
		}
		
		public function render() {			
			//render metatag			
			foreach ($this->_metaHeaders as $key => $value) {
				$this->metaHeaders .= HTML::meta($key,$value)."\n";
			}
			
			return parent::render();
		}		
	}