<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage xml
 */
final class XML {
	
	private static $xtbl_u = array('&','"','<','>');
	private static $xtbl_e = array('&#38;','&#34;','&#60;','&#62;');
	private static $xtblt_u = array('&','<','>');
	private static $xtblt_e = array('&#38;','&#60;','&#62;');
	
	/**
	 * Convenience function to load a xml file into an array.
	 * 
	 * @param  string Path or url
	 * @return array  Document structure
	 * @throws XMLParserException
	 */
	public static function load( $pathOrUrl ) {
		return self::loadString(file_get_contents($pathOrUrl));
	}
	
	/**
	 * Convenience function to load xml data into an array.
	 * 
	 * @param  string XML document
	 * @return array  Document structure
	 * @throws XMLParserException
	 */
	public static function loadString( $string ) {
		try {
			if(!($dom = simplexml_load_string($string))) {
				$e = new XMLParserException('Failed to parse XML document');
				$e->errorInfo = 'Document: '.$string;
				throw $e;
			}
			return self::simpleXMLToArray($dom);
		}
		catch(PHPException $e) {
			if(preg_match('/^[^ ]+xml[^ ]+\(/', $e->getMessage(), $m)) {
				$e = new XMLParserException($e);
				$e->errorInfo = 'Document: '.$string;
			}
			throw $e;
		}
	}
	
	/**
	 * Escape attribute value
	 * 
	 * @param  string
	 * @return string
	 * @see	   unescape()
	 */
	public static function escape($str) {
		return str_replace(self::$xtbl_u, self::$xtbl_e, $str);
	}
	
	/**
	 * Unescape attribute value
	 * 
	 * @param  string
	 * @return string
	 * @see	   escape()
	 */
	public static function unescape($str) {
		return str_replace(self::$xtbl_e, self::$xtbl_u, $str);
	}
	
	/**
	 * Escape text node
	 * 
	 * @param  string
	 * @return string
	 * @see	   unescapeText()
	 */
	public static function escapeText($str) {
		return str_replace(self::$xtblt_u, self::$xtblt_e, $str);
	}
	
	/**
	 * Unescape text node
	 * 
	 * @param  string
	 * @return string
	 * @see	   escapeText()
	 */
	public static function unescapeText($str) {
		return str_replace(self::$xtblt_e, self::$xtblt_u, $str);
	}
	
	/**
	 * @param  SimpleXMLDocument
	 * @return array  DOM structure
	 */
	public static function simpleXMLToArray(SimpleXMLElement $xml)
	{
		# new array node
		$node = array();
		
		# attributes
		if(!$xml)
			return $node;
		
		$attributes = $xml->attributes();
		if(count($attributes)) {
			foreach($attributes as $k => $v)
				$node['@'][$k] = (string) $v;
		}
		
		# child nodes
		foreach($xml as $childName => $childNode)
			$node[$childName][] = self::simpleXMLToArray($childNode);
		
		# node value
		$nodeValue = (string) $xml;
		if(trim($nodeValue) != '')
			$node['#'] = $nodeValue;
		
		return $node;
	}
}
?>