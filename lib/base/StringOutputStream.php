<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage io
 */
class StringOutputStream implements OutputStream {
	
	/** @var string */
	public $string = '';
	
	/** @var int */
	public $length = 0;
	
	/**
	 * @param  string  The buffer to use. If not specified, an string buffer is created and stored inside the instance.
	 * @throws IllegalArgumentException if $string is not a string
	 */
	public function __construct() {
		$this->string = '';
		$this->length = strlen($this->string);
	}
	
	/**
	 * @param  string
	 * @param  int
	 * @return int  Bytes written
	 */
	public function write($bytes, $length = -1)
	{
		if($length > -1)
			$bytes = substr($bytes, 0, $length);
		
		$this->string .= $bytes;
		$this->length += $len = strlen($bytes);
		return $len;
	}
	
	/**
	 * @return void
	 */
	public function close() {}
}
?>