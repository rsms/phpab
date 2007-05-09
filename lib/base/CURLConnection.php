<?
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
				print "< [BODY]\n";
			else
				print '< [BODY '.strlen($rsp)." b]\n";
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