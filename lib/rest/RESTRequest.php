<?
/**
 * REST remote call request
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage rest
 */
class RESTRequest {
	
	/** @var string Destination method name */
	public $method = '';
	
	/** @var URL */
	public $url = null;
	
	/** @var array Method call arguments in the format (string key => string value, ...) */
	public $arguments = array();
	
	/**
	 * @param  string
	 * @param  array
	 */
	public function __construct($method, &$arguments) {
		$this->method = $method;
		$this->arguments =& $arguments;
	}
	
	/** @return string response body */
	public function execute() {
		$this->url->setQuery($this->arguments);
		return file_get_contents($this->url->toString(), false);
	}
	
	/** @var string */
	public function toString()   { return get_class($this) . '['.$this->method.'#'.$this->url->toString().']'; }
	
	/** @var string */
	public function __toString() { return $this->toString(); }
}
?>