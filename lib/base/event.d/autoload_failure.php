<?
/**
 * @version    $Id$
 * @package    ab
 * @subpackage base
 */

$t = debug_backtrace();
array_shift($t);
$msg = 'Failed to load class '.$c . ".<br/>\n"
	.'Include path: "' . ini_get('include_path') . "\"<br/>";

if(ini_get('html_errors') == '0')
	$msg = strip_tags($msg);

trigger_error($msg, E_USER_ERROR);
?>