<?
/**
 * LCS is a simple, yet powerful, localization/internationalization library.
 *
 * It is similiar to gettext, but is designed for PHP and uses plain 
 * PHP-files language transalation files, making them much easier to
 * maintain than gettexts' equivalent.
 * 
 * <b>Sample Usage</b>
 * <code>
 * lcs_bind('myApp', '/path/to/localizedstuff');
 * lcs_set_locale('sv');
 * print s('Hello everyone!');
 * </code>
 *
 * This would print <samp>Hejsan allihop!</samp> if that string was 
 * translated in a file called <samp>/path/to/localizedstuff/sv/myApp.strings</samp>
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage util
 */

/** @ignore */
$__lcs_domains = array();

/**
 * Reference to current domain string array
 * @ignore
 */
$__lcs_strings = null;

/** @ignore */
$__lcs_domain = '';

/** @ignore */
$__lcs_locale = 'en';


/**
 * Bind, or unbind, domain to directory
 * 
 * If you pass null to the $dir argument, the effect will be to unbind the 
 * domain and thus freeing any loaded strings for that domain.
 * 
 * @param  string
 * @param  string
 * @param  bool
 * @return void
 */
function lcs_bind( $domain, $dir ) {
	global $__lcs_domains, $__lcs_domain;
	
	if($dir == null) {
		if(isset($__lcs_domains[$domain]))
			unset($__lcs_domains[$domain]);
	}
	else {
		if(isset($__lcs_domains[$domain])) {
			if($__lcs_domains[$domain][0] == $dir)
				return;
			$__lcs_domains[$domain][0] = $dir;
			$__lcs_domains[$domain][1] = null; // clear strings
		}
		else
			$__lcs_domains[$domain] = array($dir, null);
	}
	
	if(!$__lcs_domain)
		lcs_set_domain($domain);
}

/**
 * @param  string
 * @return string Old domain
 */
function lcs_set_domain( $domain ) {
	global $__lcs_domain, $__lcs_strings;
	
	if(empty($domain))
		return '';
	
	$old = $__lcs_domain;
	if($domain != $__lcs_domain) {
		$__lcs_domain = $domain;
		
		// Workaround to avoid clearing the old domains' strings cache
		$tmp = $__lcs_strings;
		$__lcs_strings = null;
		$__lcs_domains[$domain][1] = $tmp;
	}
	return $old;
}

/**
 * Get current domain
 * 
 * @return string
 */
function lcs_domain() {
	global $__lcs_domain;
	return $__lcs_domain;
}

/**
 * Set locale
 * 
 * <b>Note:</b> This will sequentially call <samp>setlocale(LC_ALL, $locale)</samp>
 * 
 * @param  string  Locale in 2-character notation. ie. 'en' or 'sv'.
 * @return string  Old locale or "" if none
 */
function lcs_set_locale( $locale ) {
	global $__lcs_locale, $__lcs_strings, $__lcs_domains;
	
	if($__lcs_locale == $locale)
		return $__lcs_locale;
	
	$old_locale = $__lcs_locale;
	$__lcs_locale = strtolower(substr($locale,0,2));
	
	# clear domain cache if available
	if($old_locale) {
		foreach($__lcs_domains as $domain => $v)
			$__lcs_domains[$domain][1] = null;
	}
	
	setlocale(LC_ALL, $locale);
	return $old_locale;
}

/**
 * Convert a string to the current locale equivalent.
 * 
 * @param  string
 * @return string
 * @see    s()
 * @see    lcs_dstring()
 * @see    lcs_sstring()
 */
function lcs_string( $string )
{
	global $__lcs_strings;
	
	if(!lcs_load())
		return $string;
	
	$len = strlen($string);
	$a = 0;
	$b = $len-1;
	for(;$a<$len;$a++) {
		if(($string{$a}!=' ')&&($string{$a}!='.')&&($string{$a}!=':')&&($string{$a}!="\t"))
			break;
	}
	for(;$b>-1;$b--) {
		if(($string{$b}!=' ')&&($string{$b}!='.')&&($string{$a}!=':')&&($string{$b}!="\t"))
			break;
	}
	$lkey = strtolower(substr($string,$a,$b-$a+1));
	#$lkey = strtolower(trim($string," .:\t"));
	
	$str = isset($__lcs_strings[$lkey]) ? $__lcs_strings[$lkey] : $string;
	if(ctype_upper($string{$a}))
		$str{0} = Utils::strToUpper($str{0});
	elseif(ctype_upper($str{0}))
		$str{0} = Utils::strToLower($str{0});
	
	if($a == 0 && $b == $len)
		return $str;
	elseif($a == 0)
		return $str . substr($string,$b+1);
	else
		return substr($string,0,$a) . $str . substr($string,$b+1);
}

/**
 * Make a fast, static string lookup.
 * 
 * Use this for special keys you know the exact name of and not 
 * need any casematching or stuff like that...
 * 
 * <b>Note:</b> This is case-sensitive, unlik lcs_string(), lcs_dstring() and s() 
 *              which are case-insensitive.
 * 
 * @param  string
 * @param  mixed   If null, $string will be returned if not found
 * @return string
 * @see    s()
 * @see    lcs_string()
 * @see    lcs_dstring()
 */
function lcs_sstring( $string, $default = null )
{
	global $__lcs_strings;
	
	if(!lcs_load())
		return (($default === null) ? $string : $default);
	
	return isset($__lcs_strings[$string]) ? $__lcs_strings[$string] : (($default === null) ? $string : $default);
}

/**
 * The lcs_dstring() function allows you to override the current domain for a single message lookup.
 *
 * @param  string
 * @param  string
 * @return string
 */
function lcs_dstring( $domain, $string ) {
	$old_domain = lcs_set_domain($domain);
	$string = s($string);
	lcs_set_domain($old_domain);
	return $string;
}

/**
 * The lcs_dstring() function allows you to override the current domain for a single, static message lookup.
 *
 * @param  string
 * @param  string
 * @param  mixed   If null, $string will be returned if not found
 * @return string
 */
function lcs_dsstring( $domain, $string, $default = null ) {
	$old_domain = lcs_set_domain($domain);
	$string = lcs_sstring($string, $default);
	lcs_set_domain($old_domain);
	return $string;
}

/**
 * Convert a string to the current locale equivalent.
 * 
 * You might pass variadic parameters to this function. ie. <samp>s('Loading %d bytes...', 500)</samp>
 * 
 * @param  string
 * @return string
 * @see    lcs_string()
 * @see    lcs_dstring()
 */
function s( $string )
{
	$a = func_get_args(); array_shift($a);
	if($a)
		return vsprintf(lcs_string($string), $a);
	return lcs_string($string);
}

/** @ignore */
function lcs_load()
{
	global $__lcs_strings;
	if(!$__lcs_strings) {
		if($__lcs_strings === null)
		{
			global $__lcs_domains, $__lcs_domain, $__lcs_locale;
			
			# allready loaded?
			if(isset($__lcs_domains[$__lcs_domain])) {
				if($__lcs_domains[$__lcs_domain][1]) {
					$__lcs_strings =& $__lcs_domains[$__lcs_domain][1];
				}
				else {
					$strings = null;
					if((@include $__lcs_domains[$__lcs_domain][0] . '/' . $__lcs_locale . '/' . $__lcs_domain . '.strings') === false) {
						$__lcs_strings = false;
						#trigger_error('Failed to load localized strings for domain '.$__lcs_domain, E_USER_NOTICE);
						return false;
					}
					else {
						foreach($strings as $k => $v)
							if(empty($v))
								unset($strings[$k]);
						$__lcs_strings = $strings;
						$__lcs_domains[$__lcs_domain][1] =& $__lcs_strings;
					}
				}
			}
			else {
				$__lcs_strings = false;
				return false;
			}
		}
		else {
			return false;
		}
	}
	return true;
}

?>