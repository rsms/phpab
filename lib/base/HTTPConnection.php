<?
/**
 * HTTP connection
 *
 * @version    $Id$
 * @author     Rasmus Andersson <http://hunch.se>
 * @copyright  Copyright (c) 2007 Rasmus Andersson
 * @package    ab
 * @subpackage net
 */
class HTTPConnection extends CURLConnection
{
	/** @var string */
	public $method = 'GET';
	
	/** @var array (string name => string value) Must be set before calling connect() */
	public $requestHeaders = array('User-Agent' => 'AbstractBase');
	
	
	/** @var int Number of redirects, if any, to follow (Location: responses) */
	public $followRedirects = 4;
	
	
	/** @var int Available after a successful call to connect() */
	public $responseStatus = 0;
	
	/** @var int Available after a successful call to connect() */
	public $responseStatusName = '';
	
	/** @var string Available after a successful call to connect() */
	public $responseProtocol = '';
	
	/** @var array Available after a successful call to connect() */
	public $responseHeaders = array();
	
	
	/**
	 * @param URL
	 */
	public function __construct($url=null, $extraCurlOptions=array())
	{
		parent::__construct($url, array(
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HEADERFUNCTION => array($this, '_onResponseHeader')) );
		
		if($extraCurlOptions)
			$this->curlOptions = $extraCurlOptions + $this->curlOptions;
	}
	
	
	/**
	 * @param  mixed  If request method is POST, this should be the data to post, as a string.
	 *                If request method is PUT, this should be a valid file stream resource 
	 *                and $requestBodyLength must be specified.
	 * @param  int    If request method is PUT, this must be specified and should tell how many 
	 *                bytes should be read from the  $requestBody file stream.
	 * @return mixed  If no output stream is used, returns the response body, if any. Otherwise 
	 *                a boolean is returned, indicating success.
	 * @throws IOException
	 * @throws IllegalStateException
	 * @throws IllegalOperationException
	 * @throws IllegalArgumentException
	 */
	public function connect($requestBody=null, $requestBodyLength=0)
	{
		$args = array();
		$args['requestBody'] =& $requestBody;
		$args['requestBodyLength'] =& $requestBodyLength;
		return parent::connect($args);
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
		parent::connectInit($curl, $args);
		
		$requestBody =& $args['requestBody'];
		$requestBodyLength =& $args['requestBodyLength'];
		
		# Set method, if not GET
		if($this->method != 'GET')
		{
			switch($this->method = strtoupper($this->method))
			{
				case 'POST':
					curl_setopt($curl, CURLOPT_POST, 1);
					break;
				case 'PUT':
					curl_setopt($curl, CURLOPT_PUT, 1);
					break;
				case 'GET':
					break;
				default:
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
			}
		}
		
		# Set headers
		if($this->requestHeaders)
			curl_setopt($curl, CURLOPT_HTTPHEADER, $this->requestHeaders);
		
		# PUT or not
		if($requestBody !== null && $this->method == 'PUT') {
			if(!is_resource($requestBody))
				throw new IllegalStateException('$requestBody is not a valid file stream');
			curl_setopt($curl, CURLOPT_INFILE, $requestBody);
			curl_setopt($curl, CURLOPT_INFILESIZE, $requestBodyLength);
		}
		
		# POST or not
		if($requestBody !== null && $this->method == 'POST')
			curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
		
		# Follow redirects?
		if($this->followRedirects) {
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curl, CURLOPT_MAXREDIRS, (int)$this->followRedirects);
		}
		
		# Cleared before each connection and filled by _onResponseHeader
		$this->responseProtocol = '';
		$this->responseStatus = 0;
		$this->responseStatusName = '';
		$this->responseHeaders = array();
	}
	
	/**
	 * @param  resource
	 * @param  array
	 * @return void
	 * @throws IOException
	 */
	protected function connectCleanup(&$curl, &$args)
	{
		parent::connectCleanup($curl, $args);
		$this->responseStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	}
	
	/**
	 * @param  resource
	 * @param  string
	 * @return void
	 * @internal
	 */
	public function _onResponseHeader($curl, $header)
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
	
	/** @ignore */
	public static function __test()
	{
		#parent::$debug = true;
		error_reporting(E_ALL);
		
		# Test basics and GET
		$c = new self('http://hunch.se/');
		$c->method = 'GeT';
		$c->url = 'http://apple.spotify.net/ping.php';
		$c->connect();
		assert($c->responseProtocol == '1.1');
		assert((int)($c->responseStatus/100) == 2);
		
		# Test POST
		$c->method = 'POST';
		$c->connect('unittest=1');
		
		# Test reset
		$c->url = 'http://apple.spotify.net/ping.php';
		$c->method = 'post';
		assert($c->connect('unittest=2'));
		
		# Test outputStream
		$c->outputStream = new StringOutputStream();
		$c->url = 'http://apple.spotify.net/ping.php?unittest=3';
		$c->method = 'GET';
		assert($c->connect());
		assert($c->outputStream->string != '');
		
		# Test auth
		#$c->method = 'GET';
		#$c->url = 'http://johndoe:foobar@apple.spotify.net/ui/?unittest=4';
		#$c->connect();
		
		# Test unsupported protocol error
		$c->url = 'azbx://something';
		try {
			$c->connect(); assert(!'We should have gotten an exception thrown at us');
		} catch(Exception $e) {
			assert($e instanceof IllegalArgumentException);
		}
		
		# Test connection timeout handling
		$c->url = 'http://127.126.125.124/no-such-host/';
		$c->curlOptions[CURLOPT_CONNECTTIMEOUT] = 1;
		try {
			$c->connect();
			assert(!'We should have gotten an exception thrown at us');
		} catch(Exception $e) {
			assert($e instanceof ConnectException);
		}
	}
}
?>