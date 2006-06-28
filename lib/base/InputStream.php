<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage io
 */
interface InputStream {

	/**
	 * @return bool
	 * @throws IOException
	 */
	public function isEOF();
	
	/**
	 * @param  int
	 * @return string bytes
	 * @throws IOException
	 */
	public function read($length);
	
	/**
	 * Returns a string of up to maxlength - 1 bytes read.
	 *
	 * Reading ends when maxlength - 1 bytes have been read, on a newline 
	 * (which is included in the return value), or on EOF (whichever comes first).
	 * 
	 * @param  int
	 * @return string
	 * @throws IOException
	 */
	public function readLine($maxlength = 0);
	
	/** @return void */
	public function close();
}
?>