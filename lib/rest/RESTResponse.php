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