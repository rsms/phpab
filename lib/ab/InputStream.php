<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage io
 */
interface InputStream {
	public function isEOF();
	public function read($length);
	public function readLine($maxlength = 0);
	public function close();
}
?>