<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage io
 */
class FileInputStream extends FileStream implements InputStream {
	
	/**
	 * @param  <samp>File</samp>, <samp>URL</samp> or <samp>string</samp>
	 * @throws IOException
	 */
	public function __construct($file) {
		$this->open($file, 'rb');
	}
	
	/**
	 * @return bool
	 * @throws IOException
	 */
	public function isEOF() {
		try {
			return feof($this->fd);
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'feof');
		}
	}
	
	/**
	 * @param  int
	 * @return string bytes
	 * @throws IOException
	 */
	public function read($length) {
		try {
			return fread($this->fd, $length);
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'fread');
		}
	}
	
	/**
	 * Returns a string of up to length - 1 bytes read from the file pointed to 
	 * by handle. Reading ends when length - 1 bytes have been read, on a newline 
	 * (which is included in the return value), or on EOF (whichever comes first).
	 * 
	 * If no length is specified, the length defaults to 1k, or 1024 bytes.
	 * 
	 * @param  int
	 * @return string
	 * @throws IOException
	 */
	public function readLine( $maxLength = 0 ) {
		try {
			if($maxLength > 0)
				return fgets($this->fd, $length);
			else
				return fgets($this->fd);
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'fgets');
		}
	}
	
	/**
	 * Skip bytes
	 *
	 * @param  int  Number of bytes to skip
	 * @return int  Number of bytes actually skipped
	 * @throws IOException
	 */
	public function skip( $length ) {
		try {
			return fseek($this->fd, $length, SEEK_CUR);
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'fseek');
		}
	}
}
?>