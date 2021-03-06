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
* RC4 stream cipher
*
* A very fast stream cipher, famous from it's use in WEP technology.
* Uses variable sized secret. RC4 is not patented, but it is a registered 
* trademark of RSA Security Systems.
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 */
class RC4Cipher extends CipherProxy {
	
	/**
	 * @param string
	 * @param int
	 */
	public function __construct( $key=null )
	{
		if(defined('MCRYPT_RC4'))
			$this->impl = new MCryptCipherImpl(MCRYPT_RC4, MCRYPT_MODE_STREAM, $key, false);
		else
			$this->impl = new RC4CipherImpl($key);
	}

	/** @ignore */
	public static function __test() {
		parent::__test(new self('tr7raxe5apHeTr2v'));
	}
}
?>