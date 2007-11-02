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
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage xml
 */
final class XML {
	
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
		$err_old = error_reporting(E_ALL & ~E_NOTICE);
		try {
			if(($dom = simplexml_load_string($string)) === false) {
				$e = new XMLParserException('Failed to parse XML document');
				$e->errorInfo = 'Document: '.$string;
				error_reporting($err_old);
				throw $e;
			}
			error_reporting($err_old);
			return self::simpleXMLToArray($dom);
		}
		catch(PHPException $e) {
			error_reporting($err_old);
			if(preg_match('/^[^ ]+xml[^ ]+\(/', $e->getMessage(), $m)) {
				$e = new XMLParserException($e);
				$e->errorInfo = 'Document: '.$string;
			}
			throw $e;
		}
	}
	
	/**
	 * Escape attribute value.
	 * 
	 * Encodes '"<>& and characters with ASCII value less than 32, optionally 
	 * encode other special characters (above ASCII 127).
	 * 
	 * @param  string
	 * @param  bool
	 * @return string
	 * @see	   unescape()
	 * @see	   unescape()
	 */
	public static function escape($str, $encode_high=false) {
	  return $encode_high ? 
	    filter_var($str, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_ENCODE_HIGH) :
	    filter_var($str, FILTER_SANITIZE_SPECIAL_CHARS);
	}
	
	/**
	 * Decode entities.
	 * 
	 * Decode entities previously encoded in numeric or aliased xml/html
	 * entities.
	 * 
	 * @param  string
	 * @return string
	 * @see	   escape()
	 */
	public static function unescape($str) {
		return html_entity_decode($str, ENT_QUOTES);
	}
	
	/**
	 * Escape text node.
	 * 
	 * Encodes <>&
	 * 
	 * Note: This is currently _not_ faster than {@link escape()}, use 
	 *       only if you need to preserve linebreaks, tabs, etc.
	 * 
	 * @param  string
	 * @return string
	 * @see	   escape()
	 * @see	   unescape()
	 */
	public static function escapeText($str) {
		return str_replace(self::$xtblt_u, self::$xtblt_e, $str);
	}
	
	/** @ignore */
	public static function __test() {
	  var_dump(self::escape("<>&'\"\t"));
	  #assert(self::escape("<>&'\"\t") == );
	}
	
	/**
	 * Unescape text node
	 * 
	 * @param  string
	 * @return string
	 * @see	   escapeText()
	 * @deprecated Use {@link unescape()} instead
	 */
	public static function unescapeText($str) {
		return self::unescape($str);
	}
	
	/**
	 * @param  SimpleXMLDocument
	 * @return array  DOM structure
	 */
	public static function simpleXMLToArray(SimpleXMLElement $xml) {
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