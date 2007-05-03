<?
/**
 * OOP interface to the SimpleXML extension in PHP5.
 *
 * <b>Key features</b>
 *    - Implements almost all libxml2.6+ flags using get/set methods.
 *    - Throws exceptions for all kinds of troubles. Throws parser exeption 
 *      even for older versions of libxml.
 *    - Implements a toArray() method for converting the dom to an easy
 *      accessible php array.
 *    - Implements standard __toString() and toString() wich returns the
 *      current, internal xml-dom as valid xml data.
 *
 * @version    $Id$ (050325)
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage xml
 */
class SimpleXMLParser
{	
	/**
	 * Flags for libxml (only used by php 5.1+ in conjunction with libxml 2.6+)
	 * @var int
	 */
	protected $libxmlFlags = 0;
	
	/**
	 * We keep it static so we only need to do checks once for all instances
	 * @var bool
	 */
	protected static $libxmlHasFlagSupport = false;
	
	/**
	 * The data
	 * @var string
	 */
	protected $xml = null;
	
	/**
	 * Indicates wherthere encoding should be predicted, by searching the
	 * header for known strings.
	 * @var bool
	 */
	protected $shouldPredictEncoding = false;
	
	/**
	 * Predicted encoding name, if any.
	 * @var string
	 */
	protected $predictedEncoding = null;
	
	
	/**
	 * Create a new parser instance.
	 *
	 * @throws Exception if there is no basic xml support in your PHP installation.
	 */
	public function __construct() {
		if(self::$libxmlHasFlagSupport === false) {
			if(!extension_loaded('libxml')) throw new Exception('The libxml extension is not loaded');
			if(!extension_loaded('SimpleXML')) throw new Exception('The SimpleXML extension is not loaded');
			self::$libxmlHasFlagSupport = ((version_compare(phpversion(), '5.1') != -1 ) && defined('LIBXML_VERSION'));
		}
	}
	
	
	/**
	 * @param  bool
	 * @return void
	 */
	public function setEncodingPrediction( $enabled ) {
		$this->shouldPredictEncoding = $enabled;
	}
	
	
	/**
	 * @return bool
	 */
	public function getEncodingPrediction() {
		return $this->shouldPredictEncoding;
	}
	
	
	/**
	 * @return mixed  string encoding or null if no encoding was or could not be predicted.
	 */
	public function getPredictedEncoding() {
		return $this->predictedEncoding;
	}
	
	
	/**
	 * Loads a file.
	 *
	 * @return SimpleXMLElement or null if not loaded
	 * @throws XMLParserException
	 * @throws CacheException if cache storage fails
	 * @see    enableCaching()
	 */
	public function loadFile( $file )
	{
		if($this->shouldPredictEncoding) {
			$this->predictedEncoding = self::predictEncodingOfFile($file);
		}
		
		try {
			if(self::$libxmlHasFlagSupport) {
				$this->xml = simplexml_load_file($file, 'SimpleXMLElement', $this->libxmlFlags);
			}
			else {
				$this->xml = simplexml_load_file($file);
			}
			self::checkLoaded($this->xml, 'Unknown');
		}
		catch(PHPException $e) {
			if(stripos($e->getMessage(),'not found') !== false)
				throw new FileNotFoundException($file);
			throw new XMLParserException($e);
		}
		return $this->xml;
	}
	
	
	/**
	 * @return SimpleXMLElement or null if not loaded
	 * @throws XMLParserException
	 */
	public function loadString( $string )
	{
		if($this->shouldPredictEncoding) {
			$this->predictedEncoding = self::predictEncoding($string);
		}
		ob_start();
		if(self::$libxmlHasFlagSupport) {
			$this->xml = simplexml_load_string($string, 'SimpleXMLElement', $this->libxmlFlags);
		}
		else {
			$this->xml = simplexml_load_string($string);
		}
		self::checkLoaded($this->xml, ob_get_clean());
		return $this->xml;
	}
	
	
	/**
	 * @return SimpleXMLElement or null if not loaded
	 */
	public function getXmlDocument() {
		return $this->xml;
	}
	
	
	/**
	 * @return bool
	 * @see    getLoadsDTD()
	 * @see    setLoadsDTD()
	 * @see    getValidatesDTD()
	 * @see    setValidatesDTD()
	 * @see    getRemovesBlankNodes()
	 * @see    setRemovesBlankNodes()
	 * @see    getMergesCData()
	 * @see    setMergesCData()
	 * @see    getSubstitutesEntities()
	 * @see    setSubstitutesEntities()
	 * @see    getDisableNetworkAccess()
	 * @see    setDisableNetworkAccess()
	 * @see    getRemovesRedundantNSes()
	 * @see    setRemovesRedundantNSes()
	 * @see    getUsesXInclude()
	 * @see    setUsesXInclude()
	 */
	public static function hasOptionsSupport() {
		return self::$libxmlHasFlagSupport;
	}
	
	
	// --- begin options: ---
	// Has effect only if php 5.1+ and libxml 2.6+
	
	/**
	 * @param bool
	 * @return void
	 */
	public function setLoadsDTD( $yes ) { $this->setFlag(LIBXML_DTDLOAD, $yes); }
	
	/**
	 * @param bool
	 * @return void
	 * @see hasOptionsSupport()
	 */
	public function setValidatesDTD( $yes ) { $this->setFlag(LIBXML_DTDVALID, $yes); }
	
	/**
	 * @param bool
	 * @return void
	 * @see hasOptionsSupport()
	 */
	public function setRemovesBlankNodes( $yes ) { $this->setFlag(LIBXML_NOBLANKS, $yes); }
	
	/**
	 * @param bool
	 * @return void
	 * @see hasOptionsSupport()
	 */
	public function setMergesCData( $yes ) { $this->setFlag(LIBXML_NOCDATA, $yes); }
	
	/**
	 * @param bool
	 * @return void
	 * @see hasOptionsSupport()
	 */
	public function setSubstitutesEntities( $yes ) { $this->setFlag(LIBXML_NOENT, $yes); }
	
	/**
	 * @param bool
	 * @return void
	 * @see hasOptionsSupport()
	 */
	public function setDisableNetworkAccess( $yes ) { $this->setFlag(LIBXML_NONET, $yes); }
	
	/** 
	 * @param bool
	 * @return void
	 * @see hasOptionsSupport()
	 */
	public function setRemovesRedundantNSes( $yes ) { $this->setFlag(LIBXML_NSCLEAN, $yes); }
	
	/**
	 * @param bool
	 * @return void
	 * @see hasOptionsSupport()
	 */
	public function setUsesXInclude( $yes ) { $this->setFlag(LIBXML_XINCLUDE, $yes); }
	
	
	
	/**
	 * @return bool
	 * @see hasOptionsSupport()
	 */
	public function getLoadsDTD() { return $this->usesFlag(LIBXML_DTDLOAD); }
	
	/**
	 * @return bool
	 * @see hasOptionsSupport()
	 */
	public function getValidatesDTD() { return $this->usesFlag(LIBXML_DTDVALID); }
	
	/**
	 * @return bool
	 * @see hasOptionsSupport()
	 */
	public function getRemovesBlankNodes() { return $this->usesFlag(LIBXML_NOBLANKS); }
	
	/**
	 * @return bool
	 * @see hasOptionsSupport()
	 */
	public function getMergesCData() { return $this->usesFlag(LIBXML_NOCDATA); }
	
	/**
	 * @return bool
	 * @see hasOptionsSupport()
	 */
	public function getSubstitutesEntities() { return $this->usesFlag(LIBXML_NOENT); }
	
	/**
	 * @return bool
	 * @see hasOptionsSupport()
	 */
	public function getDisableNetworkAccess() { return $this->usesFlag(LIBXML_NONET); }
	
	/**
	 * @return bool
	 * @see hasOptionsSupport()
	 */
	public function getRemovesRedundantNSes() { return $this->usesFlag(LIBXML_NSCLEAN); }
	
	/**
	 * @return bool
	 * @see hasOptionsSupport()
	 */
	public function getUsesXInclude() { return $this->usesFlag(LIBXML_XINCLUDE); }
	
	// --- end options ---
	
	/**
	 * @return string
	 */
	public function toString() { return $this->__toString(); }
	
	
	/**
	 * @return string
	 */
	public function __toString() { return $this->xml->asXML(); }
	
	
	/**
	 * @return array or null if no xml is loaded
	 */
	public function toArray() {
		return ($this->xml === null) ? null : self::toArrayWalker($this->xml);
	}
	
	
	/**
	 * Xpath for dynamic locale lookup.
	 *
	 * <b>Example:</b>
	 *
	 * <root>
	 *   <title>
	 *     <sv>Hej</sv>
	 *     <en>Hello</en>
	 *   </title>
	 * </root>
	 *
	 * ...
	 *
	 * var_dump(xpathLC($loadedXml, 'sv', 'en', '/root/title'));
	 *
	 * ...
	 *
	 * array(1) {
	 *   [0]=>
	 *   object(SimpleXMLElement)#5 (1) {
	 *     [0]=>
	 *     string(9) "Hej"
	 *   }
	 * }
	 *
	 * @param  SimpleXMLElement
	 * @param  string  ie: 'sv'  =  xpath($exp1 . 'sv' . $exp2)
	 * @param  string  ie: 'en'  =  xpath($exp1 . 'en' . $exp2)
	 * @param  string  ie: '/root/node/'
	 * @param  string  ie: '[@attr]'
	 * @param  mixed   If defined, return will be strval(first-found-node's-child-node) or $asString
	 * @param  string  ie: 'default' Will be used in third try like xpath($exp1 . $fallback . $exp2)
	 * @return mixed  if $returnStrValue is false, SimpleXMLElement, false or array(0) will be returned.
	 * @throws XMLParserException if $xml is invalid
	 */
	public static function xpathLCs( SimpleXMLElement $xml, $locale, $fbLocale, $exp1, $exp2 = '', 
	                                  $asString = false, $fallbackExp = '*' )
	{
		self::checkNotNull($xml);
		
		$r = @$xml->xpath($exp1 . $locale . $exp2);
		if($r === false || count($r) == 0) {
			$r = @$xml->xpath($exp1 . $fbLocale . $exp2);
			if($r === false || count($r) == 0) {
				$r = @$xml->xpath($exp1 . $fallbackExp . $exp2);
			}
		}
		if($asString !== false) {
			if($r !== false) {
				if(count($r) > 0) {
					$r = trim(strval($r[0]));
					if($r != '') {
						return $r;
					}
				}
			}
			return $asString;
		}
		return $r;
	}
	
	
	/**
	 * @see xpathLCs()
	 * @throws XMLParserException if no xml is loaded
	 */
	public function xpathLC( $locale, $fbLocale, $exp1, $exp2 = '', $asString = false, $fallbackExp = '*' )
	{
		return self::xpathLCs( $this->xml, $locale, $fbLocale, $exp1, $exp2, $asString, $fallbackExp );
	}
	
	
	/**
	 * @param  string
	 * @param  mixed
	 * @return mixed  boolean false on error, array(0) if no match and 
	 *                array(...){SimpleXMLElement, ...} on match
	 * @throws XMLParserException if no xml is loaded
	 */
	public function xpath( $expr, $asString = false )
	{
		self::checkNotNull($this->xml);
		
		if($asString !== false)
		{
			$r = @$this->xml->xpath($expr);
			return ($r !== false && count($r) > 0) ? strval($r[0]) : $asString;
		}
		return @$this->xml->xpath($expr);
	}
	
	
	
	// -------- privates ---------
	
	
	private static function predictEncodingOfFile( $file )
	{
		$fp = fopen($file, 'r');
		if($fp) {
			$str = @fread($fp, 155);
			@fclose($fp);
			return self::predictEncoding($str);
		}
		return null;
	}
	
	
	private static function predictEncoding( $str )
	{
		$s = stripos($str, 'encoding="');
		if($s===false) return null;
		$s += 10;
		$e = stripos($str, '"', $s);
		if($e===false) return null;
		return substr($str, $s, $e-$s);
	}
	
	/**
	 * @param  SimpleXMLDocument
	 * @return array  DOM structure
	 */
	public static function toArrayWalker(SimpleXMLElement $xml)
	{
		# new array node
		$node = array();
		
		# attributes
		$attributes = $xml->attributes();
		if(count($attributes)) {
			foreach($attributes as $k => $v)
				$node['@'][$k] = (string) $v;
		}
		
		# child nodes
		foreach($xml as $childName => $childNode)
			$node[$childName][] = self::toArrayWalker($childNode);
		
		# node value
		$nodeValue = (string) $xml;
		if(trim($nodeValue) != '')
			$node['#'] = $nodeValue;
		
		return $node;
	}
	
	
	/**
	 * @param  array   DOM structure
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return DOMDocument
	 */
	public static function arrayToXML($a, $rootNode = 'root', $version = '1.0', $encoding = null)
	{
		# typecheck
		if(!is_array($a))
			throw new IllegalStateException('$dom argument must be an array');
		
		# create document
		$doc = new DOMDocument($version, $encoding);
		
		# create root node
		$root = $doc->createElement($rootNode);
		$doc->appendChild($root);
		
		# walk
		self::arrayToXMLWalker($a, $root, $doc);
		
		# always set this to true. It costs nothing and is probably wanted later on.
		$doc->formatOutput = true;
		
		return $doc;
	}
	
	
	protected static function arrayToXMLWalker(&$a, DOMElement $n, DOMDocument $doc)
	{
		# add any attributes
		if(isset($a['@']))
		{
			if(is_array($a['@']))
				foreach($a['@'] as $k => $v)
					$n->setAttribute($k, $v);
			unset($a['@']);
		}
		
		
		# process child nodes:
		
		$childNames = array_keys($a);
		$childNames_count = count($childNames);
		
		for($x=0;$x<$childNames_count;$x++)
		{
			# we're saving memory. hurray!
			$childName =& $childNames[$x];
			$childs =& $a[$childName];
			
			# handle node value
			if($childName == '#')
			{
				$n->appendChild($doc->createTextNode($a['#']));
				unset($a['#']);
			}
			# handle child node
			else
			{
				$childs_count = count($childs);
				
				for($i=0;$i<$childs_count;$i++)
				{
					$childData =& $childs[$i];
					$childElement = $doc->createElement($childName);
					$n->appendChild($childElement);
					self::arrayToXMLWalker($childData, $childElement, $doc);
				}
			}
		}
	}
	
	
	/**
	 * Dump an overview of the structure of the xml contained within this paser.
	 * 
	 * @param  bool   Print a tree structure instead of a flat list
	 * @return string
	 */
	public function dumpStructure( $tree = false )
	{	
		if(!$tree)
			return self::dumpStructureFlattener(self::dumpStructureWalker($this->xml), '$xml');
		
		ob_start();
		print_r(self::dumpStructureWalker($this->xml));
		return ob_get_clean();
	}
	
	private static function dumpStructureFlattener($s, $prefix)
	{	
		$str = '';
		foreach($s as $k => $v) {
			if(is_array($v))
				$str .= self::dumpStructureFlattener($v, $prefix . "['$k']");
			else
				$str .= $prefix . "['$k'] = $v\n";
		}
		return $str;
	}
	
	private static function dumpStructureTypeConv($v) {
		if(is_numeric($v)) {
			eval('$v = ' . $v . ';');
			return is_int($v) ? 'int' : 'float';
		}
		elseif(preg_match('/^([0-9]{2,4}-[0-9]{2}-[0-9]{2}[T ]?[0-9]{0,2}:?[0-9]{0,2}:?[0-9]{0,2}\+?[0-9]{0,2}\.?[0-9]{0,2}|[A-Za-z]{2,3}, [0-9]{2} [A-Za-z]{2,3} [0-9]{4} [0-9]{2}:[0-9]{2}:?[0-9]{0,2} [A-Z]{0,3})$/', $v))
			return 'date';
		elseif(preg_match('/^(yes|true|no|false)$/i', trim($v)))
			return 'boolean';
		else
			return 'string';
	}
	
	private static function dumpStructureWalker($xml)
	{	
		# 1
		if ($xml instanceof SimpleXMLElement) {
			$attributes = $xml->attributes();
			foreach($attributes as $k => $v) {
				if ($v)
					$a[$k] = self::dumpStructureTypeConv((string) $v);
			}
			$x = $xml;
			$xml = get_object_vars($xml);
		}
		
		# 2
		if (is_array($xml)) {
			if (count($xml) == 0)
				return self::dumpStructureTypeConv((string) $x);
			
			foreach($xml as $key => $value) {
				
				if(is_numeric($key))
					$key = '0';
				
				$v = self::dumpStructureWalker($value);
				$r[$key] = $v;
			}
			if(isset($a))
				$r['@'] = $a;
			
			return $r;
		}
		
		# 3
		return self::dumpStructureTypeConv((string) $xml);
	}
	
	
	private function setFlag( $flag, $set = true )
	{
		if(!$set && ($this->libxmlFlags & $flag)) {
			$this->libxmlFlags -= $flag;
		}
		elseif($set && !($this->libxmlFlags & $flag)) {
			$this->libxmlFlags |= $flag;
		}
	}
	
	
	private function usesFlag( $flag )
	{
		return ($this->libxmlFlags & $flag) ? true : false;
	}
	
	
	private static function checkLoaded( &$xml, $errstr )
	{
		if($xml === false) {
			$xml = null;
			$errstr = explode("\n",trim(strip_tags($errstr)));
			$errstr = preg_replace('/^(Warning|Error):/', '', 
				preg_replace('/in [^ ]+ on line [0-9]+/', '', is_array($errstr)?$errstr[0]:$errstr));
			throw new XMLParserException("XML Parse error: $errstr");
		}
		else {
			self::checkNotNull($xml);
		}
	}
	
	
	private static function checkNotNull( &$xml )
	{
		if($xml === null) {
			throw new XMLParserException('XML Parse error: Document not loaded');
		}
	}
	
	
	/**
	 * Get all attributes in the node <samp>$node</samp> as an associative array
	 *
	 * @param  SimpleXMLElement
	 * @return array (string => string, ...)
	 */
	public static function attributesAsArray( SimpleXMLElement $node )
	{
		$atts = $node->attributes();
		$attributes = array();
		foreach($atts as $k => $v) {
			$attributes[$k] = $v;
		}
		return $attributes;
	}
}

?>