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