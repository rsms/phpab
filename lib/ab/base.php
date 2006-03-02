<?
/**
 * AB - Abstract Base
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage base
 */

error_reporting(E_ALL);
#ini_set('display_errors', '1');
#ini_set('log_errors', '0');
ini_set('ignore_repeated_errors', '1');

/**
 * Load exceptions
 * @ignore
 */
require_once 'base_exceptions.php';

/**
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    hunch.ab
 * @subpackage base
 */
class AB {
	
	public static $isSafemode = false;
	
	public static $dir = '';
	
	/** @var array */
	public static $config = array();
	
	/** @ignore */
	public static $classpath = array();
	
	/**
	 * Add a path to be searched for classes by the classloader
	 *
	 * @param  string path
	 * @return void
	 */
	public static function addClasspath( $path ) {
		if(!self::$isSafemode) {
			ini_set('include_path', ini_get('include_path') . ':' . rtrim($path,'/'));
		}
		else {
			if(!in_array($path, self::$classpath))
				$classpath[] = $path;
		}
	}
	
	/**
	 * @param  string
	 * @return bool
	 */
	public static function libraryIsLoaded($name)
	{
		$inc = self::$isSafemode ? self::$classpath : explode(':',ini_get('include_path'));
		foreach($inc as $path)
			if(strcasecmp(basename($path), $name) == 0)
				return true;
		return false;
	}
	
	/** @ignore */
	public static function onAutoloadFailure($classname) {
		$t = debug_backtrace();
		array_shift($t);
		$msg = 'Failed to load class '.$classname . ".<br/>\n"
			.'Include path: "' . ini_get('include_path') . "\"<br/>";
		
		if(ini_get('html_errors') == '0')
			$msg = strip_tags($msg);
		
		trigger_error($msg, E_USER_ERROR);
	}
	
	/**
	 * @param  string path
	 * @return void
	 */
	public static function loadConfiguration($file)
	{
		$ext = strtolower(substr(strrchr($file, '.'), 1));
		if($ext == 'xml') {
			$x = new SimpleXMLParser($file);
			$x->loadFile($file);
			self::$config = $x->toArray();
		}
		else {
			throw new IllegalFormatException('Can not load files of type "'.$ext.'"');
		}
	}
	
	/**
	 * @param  array
	 * @param  string
	 * @param  bool
	 * @return bool
	 */
	public static function configEvalBool(&$base, $nodename, $default = false) {
		if(isset($base[$nodename]))
			return preg_match('/^(true|yes|1)$/i', $base[$nodename]);
		return false;
	}
}

# __autoload
if(AB::$isSafemode = (ini_get('safe_mode') == '1')) {
	/** @ignore */
	function __autoload($c) {
		foreach(AB::$classpath as $d)
			if((@include_once "$d/$c.php") !== false)
				return;
		AB::onAutoloadFailure($c);
	}
}
else {
	/** @ignore */
	function __autoload($c) {
		if((@include_once $c . '.php') === false)
			AB::onAutoloadFailure($c);
	}
}

/** @ignore */
function __exhandler($e) {
	print ABException::format($e, true, (ini_get('html_errors') != '0'));
}
set_exception_handler('__exhandler');


/** @ignore */
function __errhandler( $errno, $str, $file, $line )
{	
	# if something was prepended by @, errlevel will be 0
	if(error_reporting() == 0)
		return;
	
	if($errno == E_WARNING || $errno == E_USER_WARNING)
		throw new PHPException($str, $errno, $file, $line);
	
	$fileLine = "on line $line in ";
	if(isset($_SERVER['DOCUMENT_ROOT']))
		$fileLine .= Utils::relativePath($file, $_SERVER['DOCUMENT_ROOT']);
	elseif(isset($GLOBALS['argv'][0]))
		$fileLine .= Utils::relativePath($file, dirname($GLOBALS['argv'][0]));
	else
		$fileLine .= $file;
	
	switch($errno) {
		case E_PARSE:
		case E_USER_ERROR:
		case E_ERROR:
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			if(ini_get('html_errors') == '0')
				print "WARNING: $str $fileLine";
			else
				print "<span class=\"warning\"><b>WARNING:</b> $str <span class=\"file\">$fileLine</span></span>";
			return;
	}
	
	if(ini_get('html_errors') == '0') {
		Utils::printError("FATAL: $str $fileLine\n\t"
			. str_replace("\n","\n\t",ABException::formatTrace(new Exception(), false, array('__errhandler')))
			. "\n");
	}
	else {
		Utils::printError("<div class=\"err\"><b>FATAL:</b> $str <span class=\"file\">$fileLine</span>\n"
			. '<div class="trace">' . ABException::formatTrace(new Exception(), true, array('__errhandler')) . '</div>'
			. '</div>');
	}
	
	exit(1);
}
set_error_handler('__errhandler', E_ALL);


# Types
#efine('T_STRING',	306);
#efine('T_ARRAY', 	357);/** @ignore */
define('T_INT',		T_DNUMBER);/** @ignore */
define('T_FLOAT',	T_LNUMBER);/** @ignore */
define('T_BOOL',	T_BOOLEAN_AND);/** @ignore */
define('T_OBJECT',	40000);/** @ignore */
define('T_RESOURCE',40001);/** @ignore */
define('T_UNKNOWN',	40002);/** @ignore */
define('T_NULL',	40003);


if(defined('DEBUG')) {
	/** @ignore */
	include_once 'debug.php';
} else {
	/** @ignore */
	function debug($a=0,$b=0){}
}

/**
 * Few-worded access to AB::$config
 * @param  string
 * @param  mixed
 * @return mixed
 */
function c( $name, $default = null ) {
	return isset(AB::$config[$name]) ? AB::$config[$name] : $default;
}

/**
 * Localization
 * @ignore
 */
require_once 'lcs.php';

AB::$dir = dirname(__FILE__);
AB::addClasspath(AB::$dir);
@include_once AB::$dir.'/../../config.php';

?>
