<?
class RESTResponse {
	public $dom = null;
	public $method = '';
	public function __construct($method, $xml) {
		$this->method = $method;
		$this->dom = SimpleXMLParser::toArrayWalker(simplexml_load_string($xml));
	}
	
	public function toString() { return get_class($this) . '['.$this->method.']'; }
	public function __toString() { return $this->toString(); }
}
?>