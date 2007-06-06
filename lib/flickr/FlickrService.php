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
 * Flickr service
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage flickr
 */
class FlickrService extends RESTServiceAdapter {
	
	/**
	 * A reference to the last instantiated FlickrService object.
	 * 
	 * <b>Example:</b>
	 * <code>
	 * $flickr = new FlickrService('mykey');
	 * ...
	 * FlickrService::$instance->call('some.method');
	 * </code>
	 * 
	 * @var FlickrService
	 */
	public static $instance = null;
	
	/** @var string */
	public $api_key = '';
	
	/**
	 * @param  string
	 */
	public function __construct($api_key)
	{
		$this->api_key = $api_key;
		iconv_set_encoding('output_encoding', 'UTF-8');
		iconv_set_encoding('input_encoding', 'UTF-8');
		iconv_set_encoding('internal_encoding', 'UTF-8');
		$this->url = new URL('http://www.flickr.com/services/rest/');
		self::$instance = $this;
	}
	
	
	/**
	 * @param  string
	 * @param  array  (string key => string value[, string key => string value[, ...]])
	 * @return RESTResponse
	 */
	public function call($method, $arguments = array())
	{
		$res = parent::call($method, $arguments);
		if($res->getStatus() == 'fail')
			throw new RESTException($res->dom['err'][0]['@']['msg'], intval($res->dom['err'][0]['@']['code']));
		return $res;
	}
	
	
	/**
	 * @param  RESTRequest
	 * @return RESTRequest
	 */
	protected function prepareRequest(RESTRequest $request)
	{
		$request->url = $this->url;
		$request->arguments['method'] = $request->method;
		$request->arguments['api_key'] = $this->api_key;
		return $request;
	}
}
?>