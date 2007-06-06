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
 * <samp>require '/path/to/ab/base/boot.php';</samp> atop your principal scripts.
 *
 * Another way doing this is to alter <samp>include_path</samp> in php.ini to include 
 * the absolute path to the directory in which boot.php resides in. Later, you can 
 * boot AbstractBase without having to know where you keep the libraries:
 * <samp>require 'boot.php';</samp>
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
 * Loading and executing this bootstrap takes about <b>0.01 ms, or 10 microseconds 
 * of user time</b> (on a celeron 2 Ghz/512 MB, debian sarge w php 5.1.4/apc 3.0.10)
 * 
 *
 * Bootstrap calltree from {@link http://pecl.php.net/package/apd APD} profiling:
 * <pre>
 * 0.00 ms   main                        0.000 ms
 * 0.00 ms   | apd_set_pprof_trace       0.000 ms
 * 0.18 ms   | require_once('boot.php')  0.182 ms
 * 0.18 ms   | | ini_get                 0.182 ms
 * 0.34 ms   | | dirname                 0.154 ms
 * 0.36 ms   | | ini_set                 0.022 ms
 * 0.38 ms   | | defined                 0.019 ms
 * 0.40 ms   | | ini_set                 0.021 ms
 * 0.42 ms   | | set_exception_handler   0.021 ms
 * 0.44 ms   | | set_error_handler       0.021 ms
 * 
 * (Times show are user + system)
 * </pre>
 *
 *
 * <br><b>The event.d directory</b>
 *
 * This directory contain partial code that is known to be rarly used.
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
 * <br><b>PHP in Safe Mode</b>
 *
 * If you are running PHP in safe mode, you need to include {@link safeboot.php} 
 * instead of this file. You should know that it is slower to run in safe mode than 
 * running in free mode. If you can, set <samp>safe_mode = Off</samp> in php.ini.
 *
 *
 * <br><b>Recommended runtime settings:</b>
 *  - docref_root = ""
 *  - ignore_repeated_errors = 1
 *  - allow_call_time_pass_reference = Off
 *  - safe_mode = Off
 *  - zend.ze1_compatibility_mode = Off
 *  - asp_tags = Off
 *  - output_buffering = Off
 * That is, settings in the <i>php.ini</i> file.
 *
 *
 * @package    ab
 * @subpackage base
 */

/** Location of abstractbase libraries */
define('AB_LIB', realpath(dirname(__FILE__).'/..'));

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
	 * @deprecated Use {@link import()} instead
	 */
	public static function addClasspath( $path ) {
		import($path);
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

# add baselib to cp
ini_set('include_path', ini_get('include_path') . ':' . AB_LIB . '/base');


if(!defined('SAFEMODE')) {

/**
 * Add a path to be searched for classes by the classloader
 *
 * @param  string Path to a directory
 * @return boolean If the include_path was successfully altered
 */
function import( $dirpath )
{
	# if no path delimiter exists, maybe it's a abstractbase library -- add both!
	if(strpos($dirpath,'/') === false)
	{
		ini_set('include_path', ini_get('include_path') . ':' . './'.$dirpath);
		return ini_set('include_path', ini_get('include_path') . ':' . AB_LIB.'/'.$dirpath) !== false;
	}
	else
	{
		return ini_set('include_path', ini_get('include_path') . ':' . $dirpath) !== false;
	}
}

/** @ignore */
function __autoload($c) {
	# we use include instead of include_once since it's alot faster
	# and the probability of including an allready included file is
	# very small.
	if((include $c . '.php') === false) {
		$t = debug_backtrace();
		if(@$t[1]['function'] != 'class_exists') {
			require_once 'event.d/autoload_failure.php';
		}
	}
} }
# TODO: move into php.ini?
ini_set('unserialize_callback_func', '__autoload');


/** @ignore */
function __exhandler($e) {
	try {
		$err = ABException::format($e, true, (ini_get('html_errors') != '0'));
	}
	catch(Exception $e) {
		$err = nl2br(strval($e));
	}
	if(ini_get('display_errors')) {
		die($err);
	}
	else {
		error_log($err);
		exit(1);
	}
}
set_exception_handler('__exhandler');


/** @ignore */
function __errhandler($errno, $str, $file, $line, &$context) {
	# if something was prepended by @, errlevel will be 0
	if(error_reporting() === 0)
		return;
	require_once 'event.d/php_error.php';
}
set_error_handler('__errhandler', E_ALL);


/**
 * Setup the logging system
 *
 * If you want to use the built-in logging system, you need to 
 * call this function at least once, in order to load the logging logic.
 * You might customize logging properties by providing custom
 * arguments. If you don't, default values are used. (see below)
 *
 * You can check if the logging has been loaded:
 * if(defined('AB_LOG'))
 *   print 'Logging is loaded';
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
 * @param  string  NAME of log file. Correct: "my_log", Wrong: "my_log.log". ".log" 
 *                 is prepended to name and written to $dir. (null = use defaults)
 * @param  string  Absoulte path to a directory in which the web server can create 
 *                 files. (null = use defaults)
 * @param  int     Decides which messages acctually get written. 0 = none,
 *                 1 = only log_error, 2 = log_error and log_warn, 3+ = everything.
 *                 (null = use defaults)
 * @param  string
 * @return void
 * @see    log_info()
 * @see    log_warn()
 * @see    log_error()
 */
function log_setup($logfile = null, $dir = null, $level = null, $msgPrefix = null) {
	require_once 'event.d/log.php';
	if($level !== null) ABLog::$level = $level;
	if($dir) ABLog::$dir = rtrim($dir,'/').'/';
	if($logfile) ABLog::$defaultFile = $logfile;
	if($msgPrefix) ABLog::$msgPrefix = $msgPrefix;
}
?>