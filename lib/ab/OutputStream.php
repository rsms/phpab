<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage io
 */
interface OutputStream {
	/**
	 * @param  string
	 * @param  int
	 * @return int  Bytes written
	 */
	public function write($bytes, $length = -1);
	
	/**
	 * @return void
	 */
	public function close();
}
?>