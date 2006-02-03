<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage io
 */
interface OutputStream {
	public function write($bytes, $length = -1);
	public function close();
}
?>