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
#ini_set('html_errors', '0');
ini_set('docref_root', '');
ini_set('ignore_repeated_errors', '1');

/**
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    hunch.ab
 * @subpackage base
 */
class AB {
	
	public static $isSafemode = false;
	
	/** @var string */
	public static $dir = '';
	
	/** @var string */
	public static $basedir = '';
	
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
	
	/** @return bool */
	public static function isCLI() {
		$n = php_sapi_name();
		return ($n == 'cli' || $n == 'cgi');
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
		elseif($ext == 'ini' || $ext == 'properties' || $ext == 'conf') {
			self::$config = parse_ini_file($file);
		}
		elseif($ext == 'dat' || $ext == 'ser' || $ext == 'serial' || $ext == 'obj') {
			self::$config = Utils::unserializeFile($file);
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
	
	/** @ignore */
	public static function onPHPError( $errno, $str, $file, $line )
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
				if(self::isCLI())
					Utils::printError("{$GLOBALS['argv'][0]}: WARNING: $str $fileLine\n");
				else
					print "<span class=\"warning\"><b>WARNING:</b> $str <span class=\"file\">$fileLine</span></span><br />";
				return;
		}
		
		if(self::isCLI()) {
			Utils::printError("{$GLOBALS['argv'][0]}: FATAL: $str $fileLine\n\t"
				. str_replace("\n","\n\t",ABException::formatTrace(new Exception(), false, array('__errhandler')))
				. "\n");
		}
		else {
			print "<div class=\"err\"><b>FATAL:</b> $str <span class=\"file\">$fileLine</span>\n"
				. '<div class="trace">' . ABException::formatTrace(new Exception(), true, array('__errhandler')) . '</div>'
				. '</div>';
		}
		
		exit(1);
	}
}

# __autoload
if(AB::$isSafemode = (ini_get('safe_mode') == '1')) {
	/** @ignore */
	function __autoload($c) {
		foreach(AB::$classpath as $d)
			if((@include_once "$d/$c.php") !== false)
				return;
		
		$t = debug_backtrace();
		if(@$t[1]['function'] != 'class_exists')
			AB::onAutoloadFailure($c);
	}
}
else {
	/** @ignore */
	function __autoload($c) {
		if((@include_once $c . '.php') === false) {
			$t = debug_backtrace();
			if(@$t[1]['function'] != 'class_exists')
				AB::onAutoloadFailure($c);
		}
	}
}
ini_set('unserialize_callback_func', '__autoload');

/** @ignore */
function __exhandler($e) { print ABException::format($e, true, (ini_get('html_errors') != '0')); }
set_exception_handler('__exhandler');
set_error_handler(array('AB','onPHPError'), E_ALL);


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

AB::$dir = dirname(__FILE__);
AB::addClasspath(AB::$dir);

// Set AB::$basedir
if(isset($_SERVER['DOCUMENT_ROOT']))
	AB::$basedir = $_SERVER['DOCUMENT_ROOT'];
else {
	if(isset($_SERVER['argv']))
		if(isset($_SERVER['argv'][0]))
			AB::$basedir = dirname(realpath($_SERVER['argv'][0]));
	if(!AB::$basedir)
		AB::$basedir = getcwd();
}


/** @ignore */
$_l2h = array('a'=>'A','b'=>'B','c'=>'C','d'=>'D','e'=>'E','f'=>'F','g'=>'G','h'=>'H','i'=>'I','j'=>'J','k'=>'K','l'=>'L','m'=>'M','n'=>'N','o'=>'O','p'=>'P','q'=>'Q','r'=>'R','s'=>'S','t'=>'T','u'=>'U','v'=>'V','w'=>'W','x'=>'X','y'=>'Y','z'=>'Z',"\xe5"=>"\xc5","\xe4"=>"\xc4","\xf6"=>"\xd6","\xe6"=>"\xc6","\xf8"=>"\xd8","\xe9"=>"\xc9","\xe8"=>"\xc8","\xe1"=>"\xc1","\xe0"=>"\xc0","\xfc"=>"\xdc","\xfb"=>"\xdb","\xf4"=>"\xd4","\xe7"=>"\xc7");
/** @ignore */
$_h2l = array('A'=>'a','B'=>'b','C'=>'c','D'=>'d','E'=>'e','F'=>'f','G'=>'g','H'=>'h','I'=>'i','J'=>'j','K'=>'k','L'=>'l','M'=>'m','N'=>'n','O'=>'o','P'=>'p','Q'=>'q','R'=>'r','S'=>'s','T'=>'t','U'=>'u','V'=>'v','W'=>'w','X'=>'x','Y'=>'y','Z'=>'z',"\xc5"=>"\xe5","\xc4"=>"\xe4","\xd6"=>"\xf6","\xc6"=>"\xe6","\xd8"=>"\xf8","\xc9"=>"\xe9","\xc8"=>"\xe8","\xc1"=>"\xe1","\xc0"=>"\xe0","\xdc"=>"\xfc","\xdb"=>"\xfb","\xd4"=>"\xf4","\xc7"=>"\xe7");

/**
 * @param  char
 * @return char
 */
function chrtolower($ch) {
	global $_h2l;
	if(isset($_h2l[$ch]))
		return $_h2l[$ch];
	return $ch;
}

/**
 * @param  char
 * @return char
 */
function chrtoupper($ch) {
	global $_l2h;
	if(isset($_l2h[$ch]))
		return $_l2h[$ch];
	return $ch;
}

?>
