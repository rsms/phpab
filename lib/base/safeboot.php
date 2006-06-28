<?
/**
 * Abstract Base bootstrap for Safe Mode
 *
 * Activates logic to simulate stuff you can't do when you are 
 * running PHP in safe-mode.
 *
 * {@link boot.php Read more...}
 *
 * @version    $Id$
 * @package    ab
 * @subpackage base
 */

require_once 'boot.php';

/**
 * @param  string
 * @return void
 * @ignore
 */
function __sm_autoload($c)
{
	foreach(explode(':', ini_get('include_path')) as $d)
		if((@include_once "$d/$c.php") !== false)
			return;
	$t = debug_backtrace();
	if(@$t[1]['function'] != 'class_exists') {
		require_once 'event.d/autoload_failure.php';
	}
}

ini_set('unserialize_callback_func', '__sm_autoload');
?>