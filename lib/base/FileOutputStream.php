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