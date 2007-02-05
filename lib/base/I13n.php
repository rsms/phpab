<?
/**
 * @package    ab
 * @subpackage util
 */
 
/**
 * Represents a internationalisation domain for use in conjunction with the I13n class
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage util
 * @access     protected
 */
class I13nDomain
{
	
	/** @var string */
	public $name = '';
	
	/** @var string */
	public $dir = '';
	
	/** @var array */
	public $strings = null;
	
	/** @param string */
	public function __construct($name, $dir) {
		$this->name = $name;
		$this->dir = $dir;
	}
	
	/**
	 * Free resources
	 * @param  string locale to free
	 * @return void
	 */
	public function freeResources($locale) {
		$this->strings = null;
	}
	
	/**
	 * @return bool  Has valid strings
	 */
	public function loadStrings()
	{
		if($this->strings !== null) {
			return true;
		}
		else if($this->strings === false) {
			return false;
		}
		else {
			if($dat = @file_get_contents($this->dir . '/' . I13n::$locale . '/strings.dat')) {
				$this->strings = unserialize($dat);
				return true;
			}
			else {
				$this->strings = false;
				return false;
			}
		}
	}
}

/**
 * Internationalisation
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage util
 */
class I13n {
	
	/** @var I13nDomain[] */
	public static $domains = array();
	
	/** @var I13nDomain */
	public static $domain = null;
	
	/** @var string */
	public static $locale = 'en';
	
	
	/**
	 * Bind, or unbind, domain to directory
	 * 
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public static function bind( $domain, $dir )
	{
		$dom = null;
		if(isset(self::$domains[$domain])) {
			$dom = self::$domains[$domain];
			if($dom->dir == $dir)
				return;
			$dom->dir = $dir;
			$dom->strings = null;
		}
		else {
			$dom = new I13nDomain($domain, $dir);
			self::$domains[$domain] = $dom;
		}
		
		if(!self::$domain)
			self::$domain = $dom;
	}
	
	/**
	 * Unbind domain, freeing any allocated resources for that domain
	 * 
	 * @param  string
	 * @return void
	 */
	public static function unbind( $domain )
	{
		if(isset(self::$domains[$domain])) {
			if(self::$domain == self::$domains[$domain])
				self::$domain = null;
			unset(self::$domains[$domain]);
		}
	}
	
	/**
	 * @param  string
	 * @return void
	 * @throws IllegalArgumentException if there is no such domain registered.
	 */
	public static function setDomain( $domain )
	{
		if(isset(self::$domains[$domain]))
			self::$domain = self::$domains[$domain];
		else
			throw new IllegalArgumentException('No such domain is registered: '.$domain);
	}
	
	/**
	 * @return string Returns "" if no active domain.
	 */
	public static function getDomain() {
		return self::$domain ? self::$domain->name : '';
	}
	
	/**
	 * Set locale
	 * 
	 * <b>Note:</b> This will sequentially call <samp>setlocale(LC_ALL, $locale)</samp>
	 * 
	 * @param  string  Locale in 2-character notation. ie. 'en' or 'sv'.
	 * @return string  Old locale or "" if none
	 */
	public static function setLocale( $locale )
	{
		if(self::$locale == $locale)
			return $locale;
		
		$old_locale = self::$locale;
		self::$locale = strtolower(substr($locale,0,2));
		
		if($old_locale)
			foreach(self::$domains as $domain)
				$domain->freeResources($old_locale);
		
		setlocale(LC_ALL, $locale);
		return $old_locale;
	}
	
	/**
	 * @return string
	 */
	public static function getLocale() {
		return self::$locale;
	}
	
	/**
	 * Convert a string to the current locale equivalent.
	 * 
	 * @param  string
	 * @return string
	 * @see    s()
	 * @see    dstring()
	 * @see    sstring()
	 */
	public static function string( $s )
	{
		if(!self::$domain)
			return $s;
		
		if(!self::$domain->loadStrings())
			return $s;
		
		$strings =& self::$domain->strings;
		$len = strlen($s);
		$a = 0;
		$b = $len-1;
		for(;$a<$len;$a++) {
			if(($s{$a}!=' ')&&($s{$a}!='.')&&($s{$a}!=':')&&($s{$a}!="\t"))
				break;
		}
		for(;$b>-1;$b--) {
			if(($s{$b}!=' ')&&($s{$b}!='.')&&($s{$a}!=':')&&($s{$b}!="\t"))
				break;
		}
		$lkey = strtolower(substr($s,$a,$b-$a+1));
		
		if(isset($strings[$lkey])) {
			$str = $strings[$lkey];
			if(ctype_upper($s{$a}))
				$str{0} = chrtoupper($str{0});
			elseif(ctype_upper($str{0}))
				$str{0} = chrtolower($str{0});
			
			if($a == 0 && $b == $len)
				return $str;
			elseif($a == 0)
				return $str . substr($s,$b+1);
			else
				return substr($s,0,$a) . $str . substr($s,$b+1);
		}
		else {
			return $s;
		}
	}
	
	/**
	 * Make a fast, static string lookup.
	 * 
	 * Use this for special keys you know the exact name of and not 
	 * need any casematching or stuff like that...
	 * 
	 * <b>Note:</b> This is case-sensitive, unlik string(), dstring() and s() 
	 *              which are case-insensitive.
	 * 
	 * @param  string
	 * @param  mixed   If null, $s will be returned if not found
	 * @return string
	 */
	public static function sstring( $s, $default = null )
	{
		if(self::$domain)
			if(self::$domain->loadStrings())
				return isset(self::$domain->strings[$s]) ? self::$domain->strings[$s] : (($default === null) ? $s : $default);
		return (($default === null) ? $s : $default);
	}
}

/**
 * Convert a string to the current locale equivalent.
 * 
 * You might pass variadic parameters to this function. ie. <samp>s('Loading %d bytes...', 500)</samp>
 * 
 * @param  string
 * @return string
 */
function s($str /*[, arg1[, arg2[, ...]]] */)
{
	if(func_num_args() > 1) {
		$a = func_get_args();array_shift($a);
		return vsprintf(I13n::string($str), $a);
	}
	return I13n::string($str);
}
?>