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
class FlickrService extends RESTService {
	
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
	
	/** @var array */
	protected static $requires_signing = array(
	  'flickr.auth.getFrob',
	  'flickr.auth.getToken',
	);
	
	
	/** @var string */
	public $api_key = '';
	
	/** @var string */
	public $shared_secret = '';
	
	/**
	 * @param  string
	 */
	public function __construct($api_key, $shared_secret=null)
	{
		$this->api_key = $api_key;
		$this->shared_secret = $shared_secret;
		#iconv_set_encoding('output_encoding', 'UTF-8');
		#iconv_set_encoding('input_encoding', 'UTF-8');
		#iconv_set_encoding('internal_encoding', 'UTF-8');
		self::$instance = $this;
	}
	
	
	/**
	 * @param  string
	 * @param  array  (string key => string value[, string key => string value[, ...]])
	 * @return RESTResponse
	 */
	public function call($method, $arguments=array(), $sign=false, $call_method='GET')
	{
	  $extra_args = array(
	    'method' => $method,
	    'api_key' => $this->api_key,
	  );
	  $arguments = is_array($arguments) ? $arguments + $extra_args : $extra_args;
	  
	  # sign call?
	  if($sign || in_array($method, self::$requires_signing)) {
	    $arguments['api_sig'] = $this->computeSignature($arguments);
	  }
	  
		$res = parent::call('http://api.flickr.com/services/rest/', $arguments, $call_method);
		if(isset($res['err']))
			throw new RESTException($res['err'][0]['@']['msg'], intval($res['err'][0]['@']['code']));
		
		return $res;
	}
	
	/**
	 * @param  array
	 * @return string
	 */
	public function computeSignature($arguments=array())
	{
    ksort($arguments);
	  $sig = $this->shared_secret;
    foreach($arguments as $k => $v)
      $sig .= strval($k) . strval($v);
    return md5($sig);
	}
}
?>