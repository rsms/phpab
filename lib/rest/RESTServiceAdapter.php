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
 * REST service client
 *
 * <b>Example</b>
 * <code>
 * $flickr = new RESTServiceAdapter('http://www.flickr.com/services/rest/');
 * echo $flickr->call('flickr.test.echo', 
 *   array('api_key' => '9b4439ce94de7e2ec2c2e6ffadc22bcf'));
 * </code>
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage rest
 */
class RESTServiceAdapter {
	
	/** @var  URL  Service endpoint url */
	protected $url = null;
	
	/**
	 * @param  mixed  <samp>URL</samp> or <samp>string</samp>
	 */
	public function __construct($url) {
		$this->url = URL::valueOf($url);
	}
	
	/**
	 * @param  string
	 * @param  array  (string key => string value[, ...])
	 * @return RESTResponse
	 */
	public function call($method, $arguments = array())
	{
		try {
			$request = $this->prepareRequest(new RESTRequest($method, $arguments));
			return new RESTResponse($method, $request->execute());
		}
		catch(PHPException $e) {
			throw new RESTException($e, 'Failed to call REST method');
		}
	}
	
	/**
	 * @param  RESTRequest
	 * @return RESTRequest
	 */
	protected function prepareRequest(RESTRequest $request) {
		$request->url = $this->url;
		$request->arguments['method'] = $request->method;
		return $request;
	}
}
?>