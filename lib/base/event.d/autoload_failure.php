<?
/**
 * @version    $Id$
 * @package    ab
 * @subpackage base
 */

$t = debug_backtrace();
array_shift($t);
$msg = '<b>Failed to load class '.$c . "</b><br/>\n"
	.'Include path: "' . ini_get('include_path') . "\"<br/>";

if(ini_get('html_errors') == '0')
	$msg = strip_tags($msg);

die($msg);
?>