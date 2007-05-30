<?
/**
 * @version    $Id$
 * @package    ab
 * @subpackage base
 */

/** @ignore */
final class ABLog
{
	public static $dir = '';
	public static $defaultFile = '';
	public static $level = 0;
	public static $msgPrefix = '';
}

/**
 * @param  $sto  int  stacktrace offset
 * @ignore
 */
function log_msg($msg, &$level, $sto)
{
	$logfile = ABLog::$defaultFile;
	
	if(!ABLog::$msgPrefix)
	{
		$f = debug_backtrace();
		$f1 = @$f[$sto+1];
		$f = $f[$sto];
		$file =& $f['file'];
		$prefix = ((strpos($file, @$_SERVER['DOCUMENT_ROOT']) === 0) ? substr($file, strlen(@$_SERVER['DOCUMENT_ROOT'])+1) : $file).':'.$f['line'];
	}
	else
		$prefix = ABLog::$msgPrefix;
	
	$args = array();
	$_msg = '';
	
	foreach($msg as $m)
	{
		if(is_object($m) and $m instanceof Exception)
			$_msg .= ABException::format($m, true, false)."\n";
		else
			$args[] = $m;
	}
	
	if(($count = count($args)))
	{
		if($count > 1) {
			$fmt = array_shift($args);
			$_msg = vsprintf($fmt, $args).($_msg ? ' '.rtrim($_msg) : '');
		}
		else {
			$_msg = $args[0];
		}
	}
	
	return error_log(date('[Y-m-d H:i:s')." $level $prefix] $_msg\n", 3, ABLog::$dir.$logfile.'.log');
}

/**
 * Log a informational message
 *
 * @param  mixed   arguments
 * @return bool    Success
 */
function log_info( /*...*/ ) {
	static $level = 'INFO ';
	if(ABLog::$level > 2)
		return log_msg(func_get_args(), $level, 1);
	return false;
}

/**
 * Log a warning message
 *
 * @param  mixed   arguments
 * @return bool    Success
 */
function log_warn( /*...*/ ) {
	static $level = 'WARN ';
	if(ABLog::$level > 1)
		return log_msg(func_get_args(), $level, 1);
	return false;
}

/**
 * Log a error message
 *
 * @param  mixed   arguments
 * @return bool    Success
 */
function log_error( /*...*/ ) {
	static $level = 'ERROR';
	if(ABLog::$level > 0)
		return log_msg(func_get_args(), $level, 1);
	return false;
}

# INIT
if($level === null) {
	$erep = error_reporting();
	ABLog::$level = ($erep & E_NOTICE ? 1:0) + ($erep & E_WARNING ? 1:0) + ($erep & E_ERROR ? 1:0);
}

if(!$dir) {
	$error_log = trim(ini_get('error_log'));
	
	if(!$error_log || $error_log == 'syslog') {
		trigger_error('log_setup(): Failed to guess log path. Using fallback "/tmp"', E_USER_NOTICE);
		ABLog::$dir = '/tmp/';
	}
	else {
		ABLog::$dir = dirname($error_log).'/';
	}
}

if(!$logfile)
	ABLog::$defaultFile = 'web';

define('AB_LOG',1);
?>