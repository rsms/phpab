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
 * Blowfish cipher
 * 
 * Blowfish is a symmetric block cipher that can be used as a drop-in replacement 
 * for DES or IDEA. It takes a variable-length key, from 32 bits to 448 bits, making 
 * it ideal for both domestic and exportable use.
 * 
 * Blowfish was designed in 1993 by Bruce Schneier as a fast, free alternative to 
 * existing encryption algorithms. Since then it has been analyzed considerably, and 
 * it is slowly gaining acceptance as a strong encryption algorithm.
 * 
 * Blowfish is unpatented and license-free, and is available free for all uses.
 * It has some known weaknesses.
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 */
class BlowfishCipher extends CipherProxy {
	
	/**
	 * @param string 56 bytes
	 * @param int
	 */
	public function __construct( $key=null, $iv=null, $mode=Cipher::MODE_CBC )
	{
		if(defined('MCRYPT_BLOWFISH'))
			$this->impl = new MCryptCipherImpl(MCRYPT_BLOWFISH, Cipher::mcryptModeForMode($mode), $key, $iv);
		else
			$this->impl = new BlowfishCipherImpl($mode, $key, $iv);
	}
	
	/** @ignore */
	public static function __test() {
		parent::__test(new self('tr7raxe5apHeTr2v'));
	}
}
?>