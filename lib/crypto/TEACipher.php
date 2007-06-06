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
 * TEA cipher.
 * 
 * This is relatively new, sufficiently strong and very compact 
 * and fast block cipher algorithm with a fixed-size 128-bit secret.
 * It is not patented.
 *
 * <b>Note:</b> As TEA uses a fixed-length secret, you should use
 * a shared secret key which is exactly 16 bytes long.
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 * @see        XTEA
 * @see        XXTEA
 */
class TEACipher extends CipherProxy {
	
	/**
	 * @param string 16 bytes
	 * @param int
	 */
	public function __construct( $key=null, $iterations=64, $mode=Cipher::MODE_CBC )
	{
		if(defined('MCRYPT_TEAN'))
			$this->impl = new MCryptCipherImpl(MCRYPT_TEAN, MCRYPT_MODE_CBC, $key);
		else
			$this->impl = new TEACipherImpl($key, $iterations);
	}
	
	/** @ignore */
	public static function __test() {
		#parent::__test(new self('tr7raxe5apHeTr2v'));
	}
}
?>