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

/** @ignore */
function log_msg($msgOrDest, $msg, &$level, $sto)
{
	if($msg) {
		$logfile = $msgOrDest;
	}
	else {
		$msg = $msgOrDest;
		$logfile = ABLog::$defaultFile;
	}
	
	if(!ABLog::$msgPrefix) {
		$f = debug_backtrace();
		$f1 = @$f[$sto+1];
		$f = $f[$sto];
		$file =& $f['file'];
		$prefix = ((strpos($file, BASEDIR) === 0) ? substr($file, strlen(BASEDIR)+1) : $file).':'.$f['line'];
	}
	else
		$prefix = ABLog::$msgPrefix;
	
	error_log(date('[Y-m-d H:i:s')." $level $prefix] $msg\n", 3, ABLog::$dir.$logfile.'.log');
}

/**
 * Log a informational message
 *
 * @param  string  Message or Logfile name
 * @param  string
 * @return void
 */
function log_info($msgOrDest, $msg = null) {
	static $level = 'INFO ';
	if(ABLog::$level > 2)
		log_msg($msgOrDest, $msg, $level, 1);
}

/**
 * Log a warning message
 *
 * @param  string  Message or Logfile name
 * @param  string
 * @return void
 */
function log_warn($msgOrDest, $msg = null) {
	static $level = 'WARN ';
	if(ABLog::$level > 1)
		log_msg($msgOrDest, $msg, $level, 1);
}

/**
 * Log a error message
 *
 * @param  string  Message or Logfile name
 * @param  string
 * @return void
 */
function log_error($msgOrDest, $msg = null) {
	static $level = 'ERROR';
	if(ABLog::$level > 0)
		log_msg($msgOrDest, $msg, $level, 1);
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
?>