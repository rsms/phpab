<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage base
 */

/** @ignore */
function c( $name, $default = null ) { global $__c; return isset($__c[$name]) ? $__c[$name] : $default; }

/** @ignore */
function c_set( $name, $value ) { global $__c; $__c[$name] = $value;}

/** @ignore */
function c_write() {
	global $c;
	$php = '<'."?\n\$__c = ".var_export($c,1).";\n?".'>';
	$php = str_replace(
		array('\'' . BASEDIR . '\'', '"' . BASEDIR . '"', '\'' . BASEDIR, '"' . BASEDIR),
		array('BASEDIR', 'BASEDIR', 'BASEDIR . \'', 'BASEDIR . "'),
		$php);
	if(file_put_contents(BASEDIR . '/config.php', $php)===false)
		throw new IOException('Failed to write configuration to ' . BASEDIR . '/config.php');
}
?>