<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage io
 */
abstract class FileStream {
	
	/** @var resource */
	public $fd = null;
	
	/** @return void */
	public function __destruct() {
		$this->close();
	}
	
	/** @return void */
	public function close() {
		@fclose($this->fd);
	}
	
	/**
	 * @param  <samp>File</samp>, <samp>URL</samp> or <samp>string</samp>
	 * @param  string
	 * @throws IOException
	 */
	protected function open($file, $mode) {
		try {
			$this->fd = fopen(File::valueOf($file)->toString(), $mode);
		} 
		catch(PHPException $e) {
			$e->rethrow('IOException', 'fopen');
		}
	}
}
?>