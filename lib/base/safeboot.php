<?
/**
 * Abstract Base bootstrap for Safe Mode
 *
 * Activates logic to simulate stuff you can't do when you are 
 * running PHP in safe-mode.
 *
 * <b>Note:</b> Loading the safe bootstrap is about 5 times slower 
 *              than loading the normal bootstrap {@link boot.php}
 *
 * @version    $Id$
 * @package    ab
 * @subpackage base
 * @see        boot.php
 */

# Classpath
$__CP = array();

/**
 * @param  string
 * @return void
 * @ignore
 */
function __autoload($c)
{
	global $__CP;
	foreach($__CP as $d) {
		if((@include_once "$d/$c.php") !== false) {
			return;
		}
	}
	$t = debug_backtrace();
	if(@$t[1]['function'] != 'class_exists') {
		require_once 'event.d/autoload_failure.php';
	}
}

/** @ignore */
function import( $dirpath ) {
	global $__CP;
	$__CP[] = $dirpath;
}

define('SAFEMODE',1);
require_once 'boot.php';
?>