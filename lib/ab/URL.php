<?
/**
 * URL - Uniform Resource Locator
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage util
 */
class URL {
	
	protected $parts;
	protected $parsedQuery = null;
	
	
	/**
	 * @param  string   <samp>{@link URL}</samp> or url as a string
	 * @throws IllegalFormatException  If url is malformed
	 */
	public function __construct( $url = null ) {
		$this->parts = array();
		if($url != null)
			$this->set($url);
	}
	
	
	/**
	 * Make sure you get a valid URL instance, where the parameter 
	 * might allready be an URL object or a string
	 * 
	 * @param  mixed  <samp>{@link URL}</samp>, <samp>{@link File}</samp> or url as a string
	 * @return URL
	 * @throws IllegalArgumentException  If $stringOrObject is neither string or URL object
	 * @throws IllegalFormatException    If url is a string and it is malformed
	 */
	public static function valueOf( $stringOrObject ) {
		if(is_object($stringOrObject)) {
			if($stringOrObject instanceof File)
				return $stringOrObject->getURL();
			if(!($stringOrObject instanceof URL))
				throw new IllegalArgumentException('url must be a string or an URL object');
			return $stringOrObject;
		}
		else
			return new URL($stringOrObject);
	}
	
	/**
	 * @return bool
	 */
	public function isEmpty() {
		return (count($this->parts) < 1);
	}
	
	/**
	 * Gets the protocol name of this <samp>URL</samp>.
	 * @param  string
	 * @return string
	 */
	public function getProtocol($default = null) { 
		return $this->getPart('scheme', $default); }
	
	/**
	 * Sets the protocol name of this <samp>URL</samp>.
	 * @param  string
	 * @return void
	 */
	public function setProtocol($protocol) { 
		$this->setPart('scheme', $protocol); }
	
	/**
	 * Check if the protocol of this <samp>URL</samp> is the 
	 * same as <samp>$protocol</samp>
	 *
	 * @param  string
	 * @param  bool
	 * @return bool
	 */
	public function isProtocol($protocol, $caseSensitive = false) { 
		if($caseSensitive)
			return ($protocol == $this->getPart('scheme'));
		else
			return (strcasecmp($protocol, $this->getPart('scheme')) == 0);
	}
	
	/**
	 * Gets the user in the authority part of this <samp>URL</samp>.
	 * @param  string
	 * @return string
	 */
	public function getUser($default = null) {
		return $this->getPart('user', $default); }
	
	/**
	 * Sets the user in the authority part of this <samp>URL</samp>.
	 * @param  string
	 * @return void
	 */
	public function setUser($user) { 
		$this->setPart('user', $user); }
	
	/**
	 * Gets the password in the authority part of this <samp>URL</samp>.
	 * @param  string
	 * @return string
	 */
	public function getPassword($default = null) {
		return $this->getPart('pass', $default); }
	
	/**
	 * Sets the password in the authority part of this <samp>URL</samp>.
	 * @param  string
	 * @return void
	 */
	public function setPassword($password) {
		$this->setPart('pass', $password); }
	
	/**
	 * Gets the host name of this <samp>URL</samp>.
	 * @param  string
	 * @return string
	 */
	public function getHost($default = null) {
		return $this->getPart('host', $default); }
	
	/**
	 * Sets the host name of this <samp>URL</samp>.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function setHost($host) {
		$this->setPart('host', $host); }
	
	/**
	 * Gets the port number of this <samp>URL</samp>.
	 * @param  int
	 * @return int
	 */
	public function getPort($default = 0) {
		return intval($this->getPart('port', $default)); }
	
	/**
	 * Sets the port number of this <samp>URL</samp>.
	 * @param  int
	 * @return void
	 */
	public function setPort($port) {
		$this->setPart('port', intval($port)); }
	
	/**
	 * Gets the path part of this <samp>URL</samp>.
	 * @param  string
	 * @return string
	 */
	public function getPath($default = null) {
		return $this->getPart('path', $default); }
	
	/**
	 * Sets the path part of this <samp>URL</samp>.
	 * @param  string
	 * @return void
	 */
	public function setPath($path) {
		$this->setPart('path', $path); }
	
	/**
	 * Gets the query part of this <samp>URL</samp>.
	 * @param  string
	 * @return string
	 */
	public function getQuery($default = null) {
		return $this->getPart('query', $default); }
	
	/**
	 * Sets the query part of this <samp>URL</samp>.
	 * @param  string
	 * @return void
	 */
	public function setQuery($query) {
		$this->setPart('query', $query);
		$this->parsedQuery = null;
	}
	
	/**
	 * Gets the query part of this <samp>URL</samp> as an associative <samp>string => string</samp> array.
	 * 
	 * @return string[]
	 */
	public function getQueryAsArray()
	{	
		if($this->parsedQuery == null) {
			$this->parsedQuery = array();
			$q = $this->getQuery();
			if($q != null) {
				$a = explode('&',$q);
				foreach($a as $part) {
					$p = strpos($part,'=');
					if($p !== false) {
						$this->parsedQuery[substr($part, 0, $p)] = substr($part, $p+1);
					}
				}
			}
		}
		return $this->parsedQuery;
	}
	
	/**
	 * Gets the anchor (also known as the "reference") of this <samp>URL</samp>.
	 * @param  string
	 * @return string
	 */
	public function getRef($default = null) { 
		return $this->getPart('fragment', $default); }
	
	/**
	 * Sets the anchor (also known as the "reference") of this <samp>URL</samp>.
	 * @param  string
	 * @return void
	 */
	public function setRef($ref) { 
		$this->setPart('fragment', $ref); }
	
	
	/**
	 * Assign <samp>URL</samp> from string
	 *
	 * @param  string
	 * @return void
	 * @throws IllegalFormatException
	 */
	public function set( $strUrl )
	{
		if(($p = @parse_url($strUrl)) === false)
			throw new IllegalFormatException('Unable to parse url');
		$this->parts = $p;
	}
	
	
	/**
	 * String representation of this <samp>URL</samp>.
	 * 
	 * @return string
	 */
	public function toString()
	{
		$str = $this->getProtocol();
		
		if($str)
			$str .= ':';
		
		if(isset($this->parts['host'])) {
			$str .= '//';
			if(isset($this->parts['user']) && isset($this->parts['pass']))
				$str .= $this->parts['user'] . ':' . $this->parts['pass'] . '@';
			elseif(isset($this->parts['user']))
				$str .= $this->parts['user'] . '@';
			elseif(isset($this->parts['pass']))
				$str .= ':' . $this->parts['pass'] . '@';
			$str .= $this->parts['host'];
			if(isset($this->parts['port']))
				$str .= ':' . $this->parts['port'];
		}
		if(isset($this->parts['path']))
			$str .= $this->parts['path'];
		if(isset($this->parts['query']))
			$str .= '?' . $this->parts['query'];
		if(isset($this->parts['fragment']))
			$str .= '#' . $this->parts['fragment'];
		
		return $str;
	}
	
	/** @return string */
	public function __toString() {
		return $this->toString();
	}
	
	/**
	 * Save content of this <samp>URL</samp> to disk
	 * 
	 * @param  double
	 * @param  double
	 * @param  bool    If true, the content will be written to a temporary file and then
	 *                 relinks the target file to the temporary one, making the write atomic.
	 *                 If false, the contents is written directly to the file. If the connection
	 *                 is slow, this may pose a threat of someone reading the file while it's
	 *                 being written to. (Results in a partial file)
	 * @param  Logger
	 * @return int  bytes written
	 */
	public function saveContent( $toFile, $connectTimeout = 10.0, $readTimeout = 30.0, $atomic = true, $debugLogger = null )
	{	
		if($debugLogger)
			$debugLogger->debug('URL::saveContent(): toFile: ' . $toFile);
		
		$req = new HttpRequest($this);
		$req->open('GET', null, $connectTimeout);
		$req->setDataTimeout($readTimeout);
		return $req->sendAndSave($toFile, null, $atomic, $debugLogger);
	}
	
	
	/** Set a part */
	private function getPart($part, $default = '') {
		return (isset($this->parts[$part])) ? $this->parts[$part] : $default;
	}
	
	/** Get a part */
	private function setPart($part, $v = null) {
		if($v == null)
			unset($this->parts[$part]);
		else
			$this->parts[$part] = $v;
	}
	
}

?>