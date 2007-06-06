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
 * cURL-based TCP connection
 * 
 * Example:
 *
 * @version    $Id$
 * @author     Rasmus Andersson <http://hunch.se>
 * @copyright  Copyright (c) 2007 Rasmus Andersson
 * @package    ab
 * @subpackage net
 * @see        http://curl.haxx.se/libcurl/ - cURL Library Homepage
 */
class CURLConnection
{
	/** @var bool */
	public static $debug = false;
	
	
	/** @var URL|string */
	public $url = null;
	
	/** @var OutputStream if set, this will receive any response body data produced by this connection. */
	public $outputStream = null;
	
	/** @var array */
	public $curlOptions = array();
	
	
	/**
	 * @param URL
	 * @param array
	 */
	public function __construct($url=null, $curlOptions=array())
	{
		$this->url = $url;
		$this->curlOptions = $curlOptions;
	}
	
	
	/**
	 * @param  array  Arguments passed along to connectInit, connectExec and connectCleanup methods.
	 * @return mixed
	 * @throws IOException
	 * @throws IllegalStateException
	 * @throws IllegalOperationException
	 * @throws IllegalArgumentException
	 */
	public function connect($args=array())
	{
		$curl = null;
		try
		{
			$this->connectInit($curl, $args);
			$rsp = $this->connectExec($curl, $args);
			$this->connectCleanup($curl, $args);
			@curl_close($curl);
			return $rsp;
		}
		catch(Exception $e)
		{	
			@curl_close($curl);
			throw $e;
		}
	}
	
	/**
	 * @param  resource
	 * @param  array
	 * @return void
	 * @throws IllegalStateException
	 * @throws IllegalOperationException
	 * @throws IllegalArgumentException
	 */
	protected function connectInit(&$curl, &$args)
	{
		$curl = curl_init(strval($this->url));
		
		# Apply options to cURL
		if(!curl_setopt_array($curl, $this->curlOptions))
			throw new IllegalOperationException(ucfirst(curl_error($curl)), curl_errno($curl));
		
		# OutputStream-based?
		if($this->outputStream)
			curl_setopt($curl, CURLOPT_WRITEFUNCTION, array($this, '_onPushOutputStream'));
		
		# Enable debug
		if(self::$debug)
			curl_setopt($curl, CURLOPT_VERBOSE, 1);
	}
	
	/**
	 * @param  resource
	 * @param  array
	 * @return mixed
	 */
	protected function connectExec(&$curl, &$args)
	{
		# Execute request
		$rsp = curl_exec($curl);
		
		# Dump response
		if(self::$debug) {
			if(is_bool($rsp))
				file_put_contents('php://stderr', "< [BODY]\n");
			elseif(strlen($rsp) > 250)
				file_put_contents('php://stderr', '< '.substr($rsp,0,250)."...\n(".(strlen($rsp)-250)." more bytes)\n");
			else
				file_put_contents('php://stderr', '< '.$rsp."\n");
		}
		
		return $rsp;
	}
	
	/**
	 * @param  resource
	 * @param  array
	 * @return void
	 * @throws IOException
	 */
	protected function connectCleanup(&$curl, &$args)
	{
		# Check errors
		if($errno = curl_errno($curl))
		{
			$excClass = 'IOException';
			switch($errno)
			{
				case CURLE_UNSUPPORTED_PROTOCOL: $excClass = 'IllegalArgumentException'; break;
				case CURLE_URL_MALFORMAT:        $excClass = 'IllegalFormatException'; break;
				case CURLE_COULDNT_RESOLVE_PROXY:
				case CURLE_COULDNT_RESOLVE_HOST:
				case CURLE_COULDNT_CONNECT:      $excClass = 'ConnectException'; break;
				case CURLE_HTTP_RANGE_ERROR:
				case CURLE_HTTP_POST_ERROR:
				case CURLE_TOO_MANY_REDIRECTS:
				case CURLE_LOGIN_DENIED:         $excClass = 'HTTPException'; break;
			}
			throw new $excClass(ucfirst(curl_error($curl)), $errno);
		}
	}
	
	/**
	 * @param  resource curl
	 * @param  string   data to be written
	 * @return int      bytes written
	 * @internal
	 */
	public function _onPushOutputStream($curl, &$data)
	{
		return $this->outputStream->write($data);
	}
	
	/** @ignore */
	public static function __test()
	{
	}
}

# curl_setopt_array first appeared in PHP 5.1.4, so lets provide a fallback.
if (!function_exists('curl_setopt_array')) {
	function curl_setopt_array(&$ch, $curl_options) {
		foreach ($curl_options as $option => $value) {
			if (!curl_setopt($ch, $option, $value)) {
				return false;
			}
		}
		return true;
	}
}
?>