<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage io
 */
class StringInputStream implements InputStream {
	
	/** @var string */
	public $string = '';
	
	/** @var int */
	public $index = 0;
	
	/** @var int */
	public $length = -1;
	
	/**
	 * @param  int
	 * @return string
	 */
	public function __construct(&$string, $fromIndex = 0, $length = -1) {
		$this->string =& $string;
		$this->index = $fromIndex;
		$this->length = ($length == -1) ? strlen($string) : $length;
	}
	
	/**
	 * @return bool
	 */
	public function isEOF() {
		return $this->index+1 == $this->length;
	}
	
	/**
	 * @param  int
	 * @return string
	 */
	public function read($length) {
		if($length == 1)
			return $this->string{$this->index++};
		
		$str = substr($this->string, $this->index, $length);
		$this->index += $length;
		return $str;
	}
	
	/**
	 * @param  int
	 * @return string
	 */
	public function readLine($maxlength = 0) {
		if(($p = strpos($this->string, "\n", $this->index)) !== false) {
			$p++;
			$str = substr($this->string, $this->index, $p - $this->index);
			$this->index = $p;
			return $str;
		}
		return substr($this->string, $this->index);
	}
	
	/**
	 * @return void
	 */
	public function close() {}
}
?>