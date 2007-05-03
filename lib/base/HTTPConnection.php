<?
/**
 * HTTP connection
 *
 * @version    $Id$
 * @author     Rasmus Andersson <http://hunch.se>
 * @copyright  Copyright (C) 2007 Spotify Technology S.A.R.L.
 * @package    ab
 * @subpackage net
 */
class HTTPConnection
{
	/** @var bool */
	public static $debug = false;
	
	/** @var resource */
	protected $curl;
	
	/** @var array */
	protected $curlOptions;
	
	/** @var array */
	protected $responseHeaders = array();
	
	/** @var OutputStream */
	protected $outputStream = null;
	
	/** @var InputStream */
	protected $inputStream = null;
	
	/** @var int Available after a successful call to connect() */
	public $responseStatus = 0;
	
	/** @var int Available after a successful call to connect() */
	public $responseStatusName = '';
	
	/** @var string Available after a successful call to connect() */
	public $responseProtocol = '';
	
	/** @var array (string name => string value) Must be set before calling connect() */
	public $requestHeaders = array();
	
	/**
	 * @param URL
	 */
	public function __construct($url=null)
	{
		$this->curl = curl_init();
		$this->curlOptions = array(
			CURLOPT_URL => '',
			CURLOPT_HEADER => 0,
			CURLOPT_USERAGENT => 'AbstractBase',
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HEADERFUNCTION => array($this, 'onResponseHeader'),
		);
		if($url)
			$this->setURL($url);
	}

	/** @ignore */
	public function __destruct()
	{
		curl_close($this->curl);
	}
	
	/** @ignore */
	public static function __test()
	{
		self::$debug = true;
		$c = new self('http://hunch.se/');
		$c->setMethod('post');
		assert($c->getMethod() == 'POST');
		$c->setURL('http://www.spotify.com/');
		$c->connect();
		assert($c->responseProtocol == '1.1');
		assert((int)($c->responseStatus/100) == 2);
	}
	
	/**
	 * @param  resource
	 * @param  string
	 * @return void
	 */
	public function onResponseHeader($curl, $header)
	{
		$len = strlen($header);
		
		if(!$this->responseProtocol) {
			# HTTP/1.1 200 OK
			$this->responseProtocol = substr($header,5,3);
			$this->responseStatusName = rtrim(substr($header, strpos($header,' ',strpos($header,' ',4)+1)+1),"\r\n");
		}
		elseif($len < 2 || $header == "\r\n") {
			return $len;
		}
		elseif(($p = strpos($header, ':')) !== false) {
			$this->responseHeaders[substr($header,0,$p)] = trim(substr($header,$p+1), " \r\n");
		}
		
		return $len;
	}
	
	/**
	 * @param  resource curl
	 * @param  string   data to be written
	 * @return int      bytes written
	 * @internal
	 */
	public function onPushOutputStream($curl, $data)
	{
		return $this->outputStream->write($data);
	}
	
	/**
	 * @param  resource curl
	 * @param  string   ??
	 * @return int      0=EOF
	 * @internal
	 */
	public function onPullInputStream($curl, $data)
	{
		print 'onPullInputStream:';
		var_dump($data);
		#return $this->outputStream->write($data);
		return 0;
	}
	
	/**
	 * Assign a stream to handle incoming response body data. If set to null, any
	 * response body data is returned as a string by the connect() method.
	 * 
	 * @param  OutputStream
	 * @return void
	 */
	public function setOutputStream($os)
	{
		if(!$os) {
			if(isset($this->curlOptions[CURLOPT_WRITEFUNCTION]))
				unset($this->curlOptions[CURLOPT_WRITEFUNCTION]);
			$this->outputStream = null;
		}
		else {
			$this->outputStream = $os;
			$this->curlOptions[CURLOPT_WRITEFUNCTION] = array($this, 'onPushOutputStream');
		}
	}
	
	/**
	 * @return OutputStream
	 */
	public function getOutputStream()
	{
		$this->outputStream;
	}
	
	/**
	 * Assign a stream to provide the request body. If null is passed, stream 
	 * input will be disabled and connect() will use $requestBody parameter if 
	 * available and/or needed.
	 * 
	 * @param  OutputStream
	 * @return void
	 */
	public function setInputStream($is)
	{
		if(!$is) {
			if(isset($this->curlOptions[CURLOPT_READFUNCTION]))
				unset($this->curlOptions[CURLOPT_READFUNCTION]);
			$this->inputStream = null;
		}
		else {
			$this->inputStream = $is;
			$this->curlOptions[CURLOPT_READFUNCTION] = array($this, 'onPullInputStream');
		}
	}
	
	/**
	 * @return InputStream
	 */
	public function getInputStream()
	{
		return $this->inputStream;
	}
	
	/**
	 * @param  URL
	 * @return void
	 */
	public function setURL($url)
	{
		$this->curlOptions[CURLOPT_URL] = URL::valueOf($url);
	}
	
	/**
	 * @return URL
	 */
	public function getURL()
	{
		return $this->curlOptions[CURLOPT_URL];
	}
	
	/**
	 * Defaults to GET
	 * 
	 * @param  string
	 * @return void
	 * @throws IllegalArgumentException
	 */
	public function setMethod($m)
	{
		static $valid = array('OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'TRACE', 'CONNECT');
		$m = strtoupper($m);
		if(!in_array($m, $valid, true))
			throw new IllegalArgumentException('Invalid request method '.$m);
		else
			$this->curlOptions[CURLOPT_CUSTOMREQUEST] = $m;
	}
	
	/**
	 * @return string
	 **/
	public function getMethod()
	{
		return $this->curlOptions[CURLOPT_CUSTOMREQUEST];
	}
	
	/**
	 * @param  bool
	 * @return void
	 */
	public function setFollowRedirects($b)
	{
		$this->curlOptions[CURLOPT_FOLLOWLOCATION] = $b ? 1 : 0;
	}
	
	/**
	 * True by default
	 * 
	 * @return bool
	 */
	public function getFollowRedirects()
	{
		return $this->curlOptions[CURLOPT_FOLLOWLOCATION] ? true : false;
	}
	
	/**
	 * @param  string  Optional. Has no effect if a input stream is used. {@see setInputStream}
	 * @return mixed   If no output stream is used, returns the response body, if any. Otherwise void is returned.
	 * @throws IOException
	 */
	public function connect($requestBody=null)
	{
		$this->responseProtocol = '';
		$this->curlOptions[CURLOPT_HTTPHEADER] =& $this->requestHeaders;
		
		curl_setopt_array($this->curl, $this->curlOptions);
		
		if(self::$debug)
			print 'DEBUG>> HTTPConnection->connect(): Request: '.(($method == 'POST') ? 'POST':'GET').' '.$this->getURL()."\n";
		
		$responseBody = curl_exec($this->curl);
		$this->responseStatus = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		
		if(self::$debug)
			print 'DEBUG>> HTTPConnection->connect(): Response: HTTP '.$this->responseStatus." string(".strlen($responseBody).")\n";
		
		if($errno = curl_errno($this->curl))
		{
			if($errno == 7)
				throw new ConnectException(ucfirst(curl_error(self::$curlHandle)), $errno);
			else
				throw new IOException(ucfirst(curl_error(self::$curlHandle)), $errno);
		}
		
		return $responseBody;
	}
}
?>