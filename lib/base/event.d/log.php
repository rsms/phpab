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
 * @version    $Id$
 * @package    ab
 * @subpackage base
 */

/** @ignore */
final class ABLog {
	public static $dir = '';
	public static $defaultFile = '';
	public static $level = 0;
	public static $msgPrefix = '';
	public static $includeTimestamp = true;
}

/**
 * @param  $sto  int  stacktrace offset
 * @ignore
 */
function log_msg($msg, &$level, $sto) {
	$logfile = ABLog::$defaultFile;
	
	if(!ABLog::$msgPrefix) {
		$f = debug_backtrace();
		$f1 = @$f[$sto+1];
		$f = $f[$sto];
		$file =& $f['file'];
		$prefix = ((strpos($file, @$_SERVER['DOCUMENT_ROOT']) === 0) ? substr($file, strlen(@$_SERVER['DOCUMENT_ROOT'])+1) : $file).':'.$f['line'];
	} else {
		$prefix = ABLog::$msgPrefix;
	}
	
	$args = array();
	$_msg2 = '';
	
	foreach($msg as $m) {
		if(is_object($m) and $m instanceof Exception) {
			$_msg2 .= ABException::format($m, true, false)."\n";
		} else {
			$args[] = $m;
		}
	}
	
	$_msg = '';
	if(($count = count($args))) {
		if($count > 1) {
			$fmt = array_shift($args);
			$_msg = vsprintf($fmt, $args).($_msg ? ' '.rtrim($_msg) : '');
		}
		else {
			$_msg = $args[0];
		}
	}
	
	if($_msg2 && $_msg) {
	  $_msg .= "\n" . $_msg2;
	}
	
	if(ABLog::$includeTimestamp) {
	  $msg = date('[Y-m-d H:i:s')." $level $prefix] $_msg";
  } else {
    $msg = "[$level $prefix] $_msg";
  }
	
	if(!$logfile) {
	  return error_log($msg, 0);
  } else {
    return error_log($msg, 3, ABLog::$dir.$logfile.'.log');
  }
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
		ABLog::$dir = '/tmp/';
	}
	else {
		ABLog::$dir = dirname($error_log).'/';
	}
}

define('AB_LOG',1);
?>