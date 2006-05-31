<?
class RESTRequest {
	public $method = '';
	public $url = null;
	public $arguments = array();
	
	public function __construct($method, &$arguments) {
		$this->method = $method;
		$this->arguments =& $arguments;
	}
	
	/** @return string response body */
	public function execute()
	{
		$this->url->setQuery($this->arguments);
		return file_get_contents($this->url->toString(), false);
	}
	
	public function toString()   { return get_class($this) . '['.$this->method.'#'.$this->url->toString().']'; }
	public function __toString() { return $this->toString(); }
}
?>