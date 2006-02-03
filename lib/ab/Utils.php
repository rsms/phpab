<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    hunch.ab
 * @subpackage base
 */
class Utils {
	
	private static $stderrFD = false;
	private static $xtbl = array('&'=>'&#38;','"'=>'&#34;','<'=>'&#60;','>'=>'&#62;');
    private static $xtblt = array('&'=>'&#38;','<'=>'&#60;','>'=>'&#62;');
	
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
     */
    public static function relativePath( $absolutePath, $relativeToBase )
    {
        $len = strlen($relativeToBase);
		if($len) {
	        if(substr($absolutePath, 0, $len) == $relativeToBase)
	            return substr($absolutePath, $len + (($relativeToBase{$len-1} != '/') ? 1 : 0));
		}
        return $absolutePath;
    }

	/**
	 * Print something to stderr
	 *
	 * @param  mixed
	 * @return void
	 */
	public static function printError( $str )
	{
		if(!self::$stderrFD)
			self::$stderrFD = fopen('php://stderr', 'w');
		fwrite(self::$stderrFD, $str);
	}
    
	
	/**
	 * Print a dump of something
	 */
	public static function dump( $data, $indentLevel = 0 ) {
		self::dumpWalker($data, $indentLevel+1);
	}
	
	/**
	 * Print a dump of the stack
	 */
	private static function dumpWalker(&$v, $level) {
		if(is_array($v)) {
			print 'Array(' . count($v) . ") {\n";
			foreach($v as $k => $v2) {
				print str_repeat('    ', $level) . '[' . self::getType($k) . ' ' . var_export($k,1) . '] = ';
				self::dumpWalker($v[$k], $level+1);
			}
			print str_repeat('    ', $level-1) . '}';
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
	 * @return object     object of new class
	 * @throws ClassCastException
	 */
	public static function classcast($obj, $toClassName) {
		$o = @unserialize(preg_replace('/^O:[0-9]+:"[^"]+":/i','O:'.strlen($toClassName).":\"$toClassName\":",serialize($obj)));
		if($o == false)
			throw new ClassCastException('Failed to convert ' . $obj . ' to class ' . $toClassName);
		return $o;
	}
	
	/**
	 * @param  mixed
	 * @param  string Any string value accepted by PHP settype()
	 * @return bool   Success
	 */
	public static function typecast( $v, $type )
	{
		if(($type == 'bool' || $type == 'boolean') && is_string($v)) {
			return (stripos($v,'true')!==false || stripos($v,'yes')!==false || stripos($v,'on')!==false || strpos($v,'1')!==false) ? true : false;
		}
		@settype($v, $type);
		return $v;
	}
	
	/**
	 * Convenience function to load a xml file into an array
	 * 
	 * @param  string
	 * @return array XMLDOM
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
     */
    public static function unserializeFile( $file ) {
        if(($dat = @file_get_contents($file)) === false)
        	throw new IOException('Failed to read file "' . $file . '"');
        if(($d = @unserialize($dat)) === false) {
        	if($dat !== serialize(false))
				throw new IOException('Failed to unserialize file "' . $file . '"');
        }
        return $d;
    }
    
    /**
     * Serialize data and write it to a file
     * 
     * @param  mixed
     * @param  string
     * @return void
     * @throws IOException
     */
    public static function serializeToFile( $data, $file ) {
    	if(($data = @serialize($data)) === false)
    		throw new IOException('Failed to serialize data');
    	if(@file_put_contents($file, $data) === false)
        	throw new IOException('Failed to write serialized data to file "' . $file . '"');
    }
	
	/**
	 * Scandinavian case conversion
	 */
	public static function strToLower($str, $utf8 = true) {
		if($utf8)
			return utf8_encode(strtr(strtolower(utf8_decode($str)),"\xc5\xc4\xd6\xc6\xd1\xd8\xd5\xdc\xc9\xca","\xe5\xe4\xf6\xe6\xf1\xf8\xf5\xfc\xe9\xea"));
		else
			return strtr(strtolower($str),"\xc5\xc4\xd6\xc6\xd1\xd8\xd5\xdc\xc9\xca","\xe5\xe4\xf6\xe6\xf1\xf8\xf5\xfc\xe9\xea");
	}
	
	/**
	 * Scandinavian case conversion
	 */
	public static function strToUpper($str) {
		return strtr(strtoupper($str),"\xe5\xe4\xf6\xe6\xf1\xf8\xf5\xfc\xe9\xea","\xc5\xc4\xd6\xc6\xd1\xd8\xd5\xdc\xc9\xca");
	}
    
    /**
     * Escape attribute value
     * 
     * @param  string
     * @return string
     * @see    xmlUnescape
     */
    public static function xmlEscape($str) {
        return strtr($str,self::$xtbl);
    }
    
    /**
     * Unescape attribute value
     * 
     * @param  string
     * @return string
     * @see    xmlEscape
     */
    public static function xmlUnescape($str) {
        return strtr($str,array_flip(self::$xtbl));
    }
    
    /**
     * Escape text node
     * 
     * @param  string
     * @return string
     * @see    xmlUnescapeText
     */
    public static function xmlEscapeText($str) {
        return strtr($str,self::$xtblt);
    }
    
    /**
     * Unescape text node
     * 
     * @param  string
     * @return string
     * @see    xmlEscapeText
     */
    public static function xmlUnescapeText($str) {
        return strtr($str,array_flip(self::$xtblt));
    }
	
	/**
	 * =?utf-8?Q?Mikael_Berggren?=  ->  Mikael Berggren
	 */
	public static function mimeStringDecode( $str ) {
		$s = imap_mime_header_decode($str);
		return utf8_encode($s[0]->text);
	}
}
?>
