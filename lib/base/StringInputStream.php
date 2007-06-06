<?
/*
Copyright (c) 2005-2007, Rasmus Andersson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
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