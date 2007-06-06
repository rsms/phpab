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
 * General utilities
 * 
 * @version	$Id$
 * @author	 Rasmus Andersson  http://hunch.se/
 * @package	ab
 * @subpackage util
 * @deprecated Replaced & decentralised (see method details for information about what you should use instead)
 */
class Utils {
	
	/**
	 * Convert absolute path to a relative path, based in <samp>$relativeToBase</samp>
	 *
	 * <b>Example</b>
	 * <code>
	 * print  Utils::relativePath('/absolute/path/to/foo.bar', '/absolute/path');
	 * prints "to/foo.bar"
	 * </code>
	 * 
	 * @param  string
	 * @param  string
	 * @return string
	 * @deprecated	Use {@link File::relativePath()} instead
	 */
	public static function relativePath( $absolutePath, $relativeToBase ) {
		return File::relativePath($absolutePath, $relativeToBase);
	}

	/**
	 * Print something to stderr
	 *
	 * @param  mixed
	 * @return void
	 * @deprecated Use {@link IO::writeError()} instead
	 */
	public static function printError($str)
	{
		IO::writeError($str);
	}
	
	
	/**
	 * Print a dump of something
	 * @deprecated Will be removed in future version
	 */
	public static function dump( $data, $indentLevel = 0 ) {
		self::dumpWalker($data, $indentLevel+1);
	}
	
	/**
	 * Print a dump of the stack
	 * @deprecated Will be removed in future version
	 */
	private static function dumpWalker(&$v, $level) {
		if(is_array($v)) {
			print 'Array(' . count($v) . ") {\n";
			foreach($v as $k => $v2) {
				print str_repeat('	', $level) . '[' . self::getType($k) . ' ' . var_export($k,1) . '] = ';
				self::dumpWalker($v[$k], $level+1);
			}
			print str_repeat('	', $level-1) . '}';
		}
		else {
			print self::getType($v) . ' ';
			if(is_object($v)) {
				if(method_exists($v, '__toString')) {
					$ov = $v->__toString();
					if(is_string($ov)) print "\"$ov\"";
					else print $ov;
				}
				elseif(method_exists($v, 'toString')) {
					$ov = $v->toString();
					if(is_string($ov)) print "\"$ov\"";
					else print $ov;
				}
				else
					echo $v;
			}
			else
				var_export($v);
		}
		print "\n";
	}
	
	/**
	 * Get variable type name
	 * 
	 * @param  mixed
	 * @return string
	 * @deprecated Will be removed in future version
	 */
	public static function getType( $v ) {
		if(is_object($v))
			return get_class($v);
		return self::normalizeTypeName(gettype($v));
	}
	
	/**
	 * Normalize type name
	 * 
	 * @param  string
	 * @return string
	 * @deprecated Will be removed in future version
	 */
	public static function normalizeTypeName( $t ) {
		$t = strtolower(substr($t,0,3));
		switch($t) {
			case 'str': return 'string';
			case 'int': return 'integer';
			case 'flo':
			case 'dou':
			case 'rea': return 'double';
			case 'voi': return 'void';
			case 'boo': return 'boolean';
			case 'mix': return 'mixed';
			case 'arr': return 'array';
			case 'obj': return 'object';
			case 'res': return 'resource';
			case 'nul': return 'null';
		}
		return $t;
	}
	
	/**
	 * Convert an object of one class to another class while keeping it's state
	 *
	 * @param  object
	 * @param  classname
	 * @return object
	 * @throws ClassCastException
	 * @deprecated Use {@link PHP::classcast()} instead
	 */
	public static function classcast($obj, $toClassName) {
		return PHP::classcast($obj, $toClassName);
	}
	
	/**
	 * @param  mixed
	 * @param  string Any string value accepted by PHP settype()
	 * @return bool   Success
	 * @deprecated	Use {@link PHP::typecast()} instead
	 */
	public static function typecast($v, $type) {
		PHP::typecast($v, $type);
	}
	
	/**
	 * Convenience function to load a xml file into an array
	 * 
	 * @param  string
	 * @return array XMLDOM
	 * @deprecated Will be removed in future version. Considering using the convenience functions provided by {@link SimpleXMLParser}
	 */
	public static function loadXML( $file ) {
		$xp = new SimpleXMLParser();
		$xp->loadFile($file);
		return $xp->toArray();
	}
	
	/**
	 * Load and unserialize file
	 * 
	 * @param  string
	 * @return mixed
	 * @throws IOException
	 * @deprecated Use {@link IO::unserializeFile()} instead
	 */
	public static function unserializeFile( $file ) {
		return IO::unserializeFile($file);
	}
	
	/**
	 * Serialize data and write it to a file
	 * 
	 * @param  mixed
	 * @param  string
	 * @return void
	 * @throws IOException
	 * @deprecated Use {@link IO::serialize()} instead
	 */
	public static function serializeToFile( $data, $file ) {
		IO::serialize($data, $file);
	}
	
	/**
	 * Escape attribute value
	 * 
	 * @param  string
	 * @return string
	 * @deprecated Use {@link XML::escape()} instead
	 */
	public static function xmlEscape($str) {
		return XML::escape($str);
	}
	
	/**
	 * Unescape attribute value
	 * 
	 * @param  string
	 * @return string
	 * @deprecated Use {@link XML::unescape()} instead
	 */
	public static function xmlUnescape($str) {
		return XML::unescape($str);
	}
	
	/**
	 * Escape text node
	 * 
	 * @param  string
	 * @return string
	 * @deprecated Use {@link XML::escapeText()} instead
	 */
	public static function xmlEscapeText($str) {
		return XML::escapeText($str);
	}
	
	/**
	 * Unescape text node
	 * 
	 * @param  string
	 * @return string
	 * @deprecated Use {@link XML::escapeText()} instead
	 */
	public static function xmlUnescapeText($str) {
		return XML::unescapeText($str);
	}
	
	/**
	 * @param      string
	 * @return     string
	 * @deprecated Use {@link IMAP::mimeStringDecode()} instead
	 */
	public static function mimeStringDecode( $str ) {
		# we can't just redirect to IMAP::mimeStringDecode() because that's a separate library.
		$s = imap_mime_header_decode($str);
		return utf8_encode($s[0]->text);
	}
	
	private static $l2h = array('a'=>'A','b'=>'B','c'=>'C','d'=>'D','e'=>'E','f'=>'F','g'=>'G','h'=>'H','i'=>'I','j'=>'J','k'=>'K','l'=>'L','m'=>'M','n'=>'N','o'=>'O','p'=>'P','q'=>'Q','r'=>'R','s'=>'S','t'=>'T','u'=>'U','v'=>'V','w'=>'W','x'=>'X','y'=>'Y','z'=>'Z',"\xe5"=>"\xc5","\xe4"=>"\xc4","\xf6"=>"\xd6","\xe6"=>"\xc6","\xf8"=>"\xd8","\xe9"=>"\xc9","\xe8"=>"\xc8","\xe1"=>"\xc1","\xe0"=>"\xc0","\xfc"=>"\xdc","\xfb"=>"\xdb","\xf4"=>"\xd4","\xe7"=>"\xc7");

	private static $h2l = array('A'=>'a','B'=>'b','C'=>'c','D'=>'d','E'=>'e','F'=>'f','G'=>'g','H'=>'h','I'=>'i','J'=>'j','K'=>'k','L'=>'l','M'=>'m','N'=>'n','O'=>'o','P'=>'p','Q'=>'q','R'=>'r','S'=>'s','T'=>'t','U'=>'u','V'=>'v','W'=>'w','X'=>'x','Y'=>'y','Z'=>'z',"\xc5"=>"\xe5","\xc4"=>"\xe4","\xd6"=>"\xf6","\xc6"=>"\xe6","\xd8"=>"\xf8","\xc9"=>"\xe9","\xc8"=>"\xe8","\xc1"=>"\xe1","\xc0"=>"\xe0","\xdc"=>"\xfc","\xdb"=>"\xfb","\xd4"=>"\xf4","\xc7"=>"\xe7");

	
	/**
	 * @param  char
	 * @return char
	 * @deprecated Will be removed in future version.
	 */
	public static function chrToLower($ch)
	{
		if(isset(self::$h2l[$ch]))
			return self::$h2l[$ch];
		return $ch;
	}
	
	/**
	 * @param  char
	 * @return char
	 * @deprecated Will be removed in future version.
	 */
	public static function chrToUpper($ch)
	{
		if(isset(self::$l2h[$ch]))
			return self::$l2h[$ch];
		return $ch;
	}
	
	/**
	 * @param  string
	 * @param  bool
	 * @return string
	 * @deprecated Will be removed in future version.
	 */
	public static function strToLower($str, $utf8 = false) {
		if($utf8)
			return utf8_encode(strtr(strtolower(utf8_decode($str)),"\xc5\xc4\xd6\xc6\xd1\xd8\xd5\xdc\xc9\xca","\xe5\xe4\xf6\xe6\xf1\xf8\xf5\xfc\xe9\xea"));
		else
			return strtr(strtolower($str),"\xc5\xc4\xd6\xc6\xd1\xd8\xd5\xdc\xc9\xca","\xe5\xe4\xf6\xe6\xf1\xf8\xf5\xfc\xe9\xea");
	}
	
	/**
	 * @param  string
	 * @return string
	 * @deprecated Will be removed in future version.
	 */
	public static function strToUpper($str, $length = -1)
	{
		return strtr(strtoupper($str),"\xe5\xe4\xf6\xe6\xf1\xf8\xf5\xfc\xe9\xea","\xc5\xc4\xd6\xc6\xd1\xd8\xd5\xdc\xc9\xca");
	}
	
	/**
	 * @param  array
	 * @param  string
	 * @param  string
	 * @return string "{key=>value, key=>value...}"
	 * @deprecated Will be removed in future version.
	 */
	public static function hash_to_string($array, $concator = '=>', $separator = ', ')
	{
		if(!is_array($array)) return '';
		if(!$array)		   return '{}';
		$str = '{';
		foreach($array as $k => $v) {
			$str .= (is_int($k) || is_double($k)) ? $k : "'".strval($k)."'";
			$str .= $concator;
			if(is_array($v)) 
				$str .= self::hash_to_string($v);
			elseif(is_int($v) || is_double($v))
				$str .= $v;
			else
				$str .= "'".strval($v)."'";
			$str .= $separator;
		}
		return ($separator ? substr($str, 0, -strlen($separator)) : $str) . '}';
	}
}
?>
