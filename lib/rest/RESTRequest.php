<?
/*
Copyright (c) 2005-2007, Rasmus Andersson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
/**
 * REST remote call request
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
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