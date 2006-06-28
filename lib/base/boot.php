<?
/**
 * Abstract Base bootstrap
 *
 * <b>Includes</b>
 *   - Dynamic classloader
 *   - Error handling
 *   - Exception handling
 *   - Basic logging system
 *
 *
 *
 * <br><b>Usage</b>
 *
 * There are several ways of bootstrapping your AbstractBase based application. 
 * One of them is to simply put 
 * <samp>require_once '/path/to/ab/base/boot.php';</samp> atop your principal scripts.
 *
 * Another way doing this is to alter <samp>include_path</samp> in php.ini to include 
 * the absolute path to the directory in which boot.php resides in. Later, you can 
 * boot AbstractBase without having to know where you keep the libraries:
 * <samp>require_once 'boot.php';</samp>
 * 
 *
 * <br><b>Performance</b>
 *
 * AbstractBase is written with performance in mind. That is, less convenience 
 * functionality, higher speed. It is <i>STRONGLY RECOMMENDED</i> to use 
 * {@link http://pecl.php.net/package/APC APC opcache}, A compiled-code 
 * in-memory cache, made by the folks who created and develops the PHP language.
 * You will want to keep AbstractBase in a central location if you use an opcode 
 * cache, like APC. That way, you gain alot of speed as all your applications use 
 * the same, memory-stored, code.
 * <b>Dont use {@link http://trac.lighttpd.net/xcache/ xcache}</b>, it 
 * still has some issues with PHP 5.0/5.1.
 *
 *
 * <br><b>The event.d directory</b>
 *
 * This directory contains partial code that is not known to be used in every session. 
 * It's is automatically loaded upon first request. Logic using this sort of 
 * optimisation includes error handling and exception handling, as it is sparsely used.
 *
 *
 * <br><b>Built-in Logging</b>
 *
 * <i>A simple, built-in logging system</i> is included in Abstract Base as a event.d 
 * type. Writing a message to one of the <samp>log</samp> functions results in a line 
 * with the format:<br>
 * <samp>[YYYY-MM-DD HH:MM:MM LEVEL File:Line] Message<LF></samp>.<br>
 * Regular expressions pattern:<br>
 * <samp>^\[(([0-9-]{10}) ([0-9-]{8})) ([A-Z]+) +(([^:]+):([0-9]+))\] (.*)[\r\n]*$</samp>
 *
 * Have a look at {@link log_setup()} for more details.
 *
 *
 * <br><b>PHP setup</b>
 *
 * If you are running PHP in safe mode, you need to include {@link safeboot.php} 
 * instead of this file. You should know that it is slower to run in safe mode than 
 * running in free mode. If you can, set <samp>safe_mode = Off</samp> in php.ini.
 * <br><br>
 *
 * Recommended php.ini settings:
 *  - docref_root = ""
 *  - ignore_repeated_errors = 1
 *  - allow_call_time_pass_reference = Off
 *  - safe_mode = Off
 *  - zend.ze1_compatibility_mode = Off
 *  - asp_tags = Off
 *  - output_buffering = Off
 *
 *
 * @package    ab
 * @subpackage base
 */


/**
 * Runtime utilities
 *
 * <b>Note:</b> This is always loaded in Abstract Base, bacause it is 
 *              (the only) class required for booting Abstract Base.
 *
 * <b>Features</b>
 *  - Classpath association
 *  - Library lookup
 *  - PHP Runtime type (CLI or embedded)
 *  - Type- and classcasting
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage base
 */
class PHP {
	
	/**
	 * Add a path to be searched for classes by the classloader
	 *
	 * @param  string path
	 * @return void
	 */
	public static function addClasspath( $path ) {
		ini_set('include_path', ini_get('include_path') . ':' . rtrim($path,'/'));
	}
	
	/**
	 * Find out if a library is loaded or not
	 *
	 * <b>Note:</b> This operation is pretty time-consuming if <samp>$insensitive</samp> is true.
	 * 
	 * @param  string Name or Path
	 * @param  bool   If true, 'myLib' will match '/www/lib/myLib/' and './myLIB'. 
	 *                If false, the path must be a perfect match.
	 * @return bool
	 */
	public static function libraryIsLoaded($name, $insensitive = false)
	{
		#$inc = self::$isSafemode ? self::$classpath : explode(':',ini_get('include_path'));
		$inc = explode(':',ini_get('include_path'));
		
		if($insensitive) {
			if(strpos($name,'/') !== false)
				$name = basename($name);
			foreach($inc as $path)
				if(strcasecmp(basename($path), $name) == 0)
					return true;
		}
		else {
			foreach($inc as $path)
				if($path == $name)
					return true;
		}
		
		return false;
	}
	
	/** @return bool */
	public static function isCLI() {
		$n = php_sapi_name();
		return ($n == 'cli' || $n == 'cgi');
	}
	
	/**
	 * Convert an object of one class to another class while keeping it's state
	 *
	 * @param  object
	 * @param  classname
	 * @return object
	 * @throws ClassCastException
	 */
	public static function classcast($obj, $toClass)
	{
		if(($o = @unserialize(preg_replace('/^O:[0-9]+:"[^"]+":/i','O:'.strlen($toClass).":\"$toClass\":",serialize($obj)))) === false)
			throw new ClassCastException('Failed to convert ' . $obj . ' to class ' . $toClass);
		return $o;
	}
	
	/**
	 * @param  mixed
	 * @param  string Any string value accepted by PHP {@link http://php.net/settype() settype()}
	 * @return bool   Success
	 */
	public static function typecast( $v, $toType )
	{
		if(($toType == 'bool' || $toType == 'boolean') && is_string($v))
			return stripos($v,'true')!==false || stripos($v,'yes')!==false || stripos($v,'on')!==false || strpos($v,'1')!==false;
		
		@settype($v, $toType);
		return $v;
	}
}

# Append base lib to include path
ini_set('include_path', ini_get('include_path') . ':' . dirname(__FILE__));

/** @ignore */
function __autoload($c) {
	if((@include_once $c . '.php') === false) {
		$t = debug_backtrace();
		if(@$t[1]['function'] != 'class_exists') {
			require_once 'event.d/autoload_failure.php';
		}
	}
}
# TODO: Spray: move into php.ini?
ini_set('unserialize_callback_func', '__autoload');


/** @ignore */
function __exhandler($e) {
	print ABException::format($e, true, (ini_get('html_errors') != '0'));
}
set_exception_handler('__exhandler');


/** @ignore */
function __errhandler($errno, $str, $file, $line, &$context) {
	require_once 'event.d/php_error.php';
}
set_error_handler('__errhandler', E_ALL);


/**
 * Setup the logging system
 *
 * If you want to use the built-in logging system, you need to 
 * call this function once, in order to load the logging logic.
 * You might customize logging properties by providing custom
 * arguments. If you don't, default values are used. (see below)
 *
 * <br><b>Default values</b>
 *   - <samp>$dir = dirname(ini_get('error_log'))</samp> or /tmp if error_log is not set or points to syslog.
 *   - <samp>$defaultLogfile = 'web'</samp>
 *   - <samp>$level = (error_reporting() & E_NOTICE ? 1:0) + (error_reporting() & E_WARNING ? 1:0) + (error_reporting() & E_ERROR ? 1:0);</samp>
 * 
 * <br><b>Value of default Level</b>
 *   - Level is 3 (everything) if E_NOTICE, E_WARNING and E_ERROR is included in error reporting.
 *   - Level is 2 (warnings and errors) if E_WARNING and E_ERROR is included in error reporting, but not E_NOTICE.
 *   - Level is 1 (only errors) if only E_ERROR is included in error reporting
 *   - Level is 0 (nothing) if neither E_NOTICE, E_WARNING or E_ERROR is included in error reporting.
 *
 * @param  string  Absoulte path to a directory in which the web server can create 
 *                 files. (null = use defaults)
 * @param  string  NAME of log file. Correct: "my_log", Wrong: "my_log.log". ".log" 
 *                 is prepended to name and written to $dir. (null = use defaults)
 * @param  int     Decides which messages acctually get written. 0 = none,
 *                 1 = only log_error, 2 = log_error and log_warn, 3+ = everything.
 *                 (null = use defaults)
 * @return void
 */
function log_setup($dir = null, $defaultLogfile = null, $level = null) {
	require_once 'event.d/log.php';
	if($level !== null) ABLog::$level = $level;
	if($dir) ABLog::$dir = rtrim($dir,'/').'/';
	if($defaultLogfile) ABLog::$defaultFile = $defaultLogfile;
}
?>