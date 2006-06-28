<?
/**
 * REST remote call response
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage rest
 */
class RESTResponse {
	
	/** @var array */
	public $dom = null;
	
	/** @var string Name of the method responsible for the response */
	public $method = '';
	
	/**
	 * @param  string
	 * @param  string  Raw XML response
	 */
	public function __construct($method, $xml) {
		$this->method = $method;
		$this->dom = SimpleXMLParser::toArrayWalker(simplexml_load_string($xml));
	}
	
	/**
	 * Response status
	 * For example: "ok" or "fail"
	 * 
	 * @return string
	 */
	public function getStatus() {
		return @$this->dom['@']['stat'];
	}
	
	/** @return string */
	public function toString() {
		$dom_temp = $this->dom;
		$dom_temp['@']['method'] = $this->method;
		return SimpleXMLParser::arrayToXML($dom_temp, 'rsp', '1.0', 'iso-8859-1')->saveXML();
	}
	
	/** @return string */
	public function __toString() { return $this->toString(); }
}
?>