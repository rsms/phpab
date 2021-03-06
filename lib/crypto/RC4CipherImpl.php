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
 * Highly optimised PHP code. Alot of op using APD has been made.
 * Don't use this for heavy encryption, as it's fairly unsafe and
 * quite easy to break. Good for short term encryption.
 *
 * <b>Example</b><code>
 * $key = "pelle";
 * $data = "Bosse rulez!";
 *
 * $rc4 = new RC4Cipher( $key );
 * echo "Original data: $data <br>\n";
 * $data = $rc4->encrypt($data);
 * echo "Encrypted data: $data <br>\n";
 * $data = $rc4->decrypt($data);
 * echo "Decrypted data: $data <br>\n";
 * </code>
 *
 * @version	$Id$
 * @package	ab
 * @subpackage crypto
 * @author	 Rasmus Andersson <http://hunch.se/>
 * @author	 Dave Mertens <dmertens.AT.zyprexia.com>
 * @author	 Damien Miller <djm.AT.mindrot.org>
 */
class RC4CipherImpl extends Cipher {

	/** @access private */
	private $s = array();
	
	/** @access private */
	private $i = 0;
	
	/** @access private */
	private $j = 0;

	/** @var string */
	private $rawSecret;
	
	/** @var int bits */
	private $rawSecretSize;


	/**
	* @param  string  $key
	* @param  int	 $flags
	* @param  string  $key_bit_length  Take a look at {@see setKeyBitLength}()
	* @return void
	* @access public
	*/
	public function __construct($key=null) {
		if($key)
			$this->setKey($key);
	}
	
	/**
	 * {@inheritdoc}
	 *
	 * @param  string
	 * @return void
	 * @throws IllegalArgumentException  if key is empty
	 */
	public function setKey($key) {
		if(!$key)
			throw new IllegalArgumentException('Key can not be empty');
		$this->key = $key;
		$this->rawSecretSize = strlen($key) * 8;
	}
	

	/**
	 * {@inheritdoc}
	 *
	 * @param  string
	 * @return string
	 */
	public function encrypt( $data ) {
		# same function for both directions
		return $this->decrypt($data);
	}
	
	
	/**
	 * {@inheritdoc}
	 *
	 * @param  string
	 * @return string
	 */
	public function decrypt( $data )
	{
		//Init key for every call
		$this->calcRC4Secret();
		
		$i = 0;
		$j = 0;
		$len = strlen($data);
		$keyLen =& $this->rawSecretSize;
		
		for ($c= 0; $c < $len; $c++)
		{
			$this->i = ($this->i + 1) % $keyLen;
			$this->j = ($this->j + $this->s[$this->i]) % $keyLen;
			$t = $this->s[$this->i];
			$this->s[$this->i] = $this->s[$this->j];
			$this->s[$this->j] = $t;

			$t = ($this->s[$this->i] + $this->s[$this->j]) % $keyLen;

			$data{$c} = chr(ord($data{$c}) ^ $this->s[$t]);
		}
		return $data;
	}
	
	
	/**
	* Assign encryption key to class
	*
	* @param  string key	- Key which will be used for encryption
	* @return void
	* @access private  
	*/
	private function calcRC4Secret()
	{
		if(!$this->key)
			throw new IllegalStateException('Key is undefined');
		$key =& $this->key;
		$keyLen =& $this->rawSecretSize;
		$len = strlen($key);
		
		for ($this->i = 0; $this->i < $keyLen; $this->i++)
			$this->s[$this->i] = $this->i;

		$this->j = 0;
		
		for ($this->i = 0; $this->i < $keyLen; $this->i++) {
			$this->j = ($this->j + $this->s[$this->i] + ord($key[$this->i % $len])) % $keyLen;
			$t = $this->s[$this->i];
			$this->s[$this->i] = $this->s[$this->j];
			$this->s[$this->j] = $t;
		}
		$this->i = $this->j = 0;
	}
	
	
	/** @ignore */
	public static function __test() {
		parent::__test(new self('abcdefghijklmnop'));
	}
}
?>