<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage io
 */
class FileOutputStream extends FileStream implements OutputStream {
	
	/**
	 * @param  <samp>File</samp>, <samp>URL</samp> or <samp>string</samp>
	 * @throws IOException
	 */
	public function __construct($file) {
		$this->open($file, 'wb');
	}
	
	/**
	 * @param  string
	 * @param  int
	 * @return int  Bytes written
	 */
	public function write($bytes, $length = -1) {
		try {
			if($length > -1)
				return fwrite($this->fd, $bytes, $length);
			else
				return fwrite($this->fd, $bytes);
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'fwrite');
		}
	}
}
?>