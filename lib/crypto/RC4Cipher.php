<?
/**
 * RC4 stream cipher routines implementation
 *
 * An extremly fast stream cipher, famous from it's use in WEP technology.
 * 
 * Uses variable sized secret. RC4 is not patented, but it is a registered 
 * trademark of RSA Security Systems. Based on code written by 
 * Damien Miller <djm@mindrot.org>.
 *
 * Highly optimised PHP code. Alot of op using APD has been made.
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
class RC4Cipher extends Cipher {

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
	public function __construct($secret) {
		$this->setSecret($secret);
		$this->calcRC4Secret();
	}

	
	/**
	 * {@inheritdoc}
	 *
	 * @param  string
	 * @return void
	 * @throws IllegalArgumentException  if secret is empty/null
	 */
	public function setSecret($secret) {
		if(!$secret)
			throw new IllegalArgumentException('Secret can not be empty');
		
		$this->secret = $secret;
		$this->rawSecretSize = strlen($secret) * 8;
	}
	

	/**
	 * {@inheritdoc}
	 *
	 * @param  string
	 * @return string
	 */
	public function encrypt( $data ) {
		return $this->postFilterData($this->crypt($data));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param  string
	 * @return string
	 */
	public function decrypt ( $data ) {
		return $this->crypt($this->preFilterData($data));
	}
	
	
	/// PRIVATES ///////////////////////////////////////////////
	
	
	/**
	 * @param  string
	 * @return string
	 */
	protected function crypt( $str ) {

		//Init key for every call
		$this->calcRC4Secret();
		
		$i = 0;
		$j = 0;
		$len = strlen($str);
		$keyLen =& $this->rawSecretSize;
		
		for ($c= 0; $c < $len; $c++)
		{
			$this->i = ($this->i + 1) % $keyLen;
			$this->j = ($this->j + $this->s[$this->i]) % $keyLen;
			$t = $this->s[$this->i];
			$this->s[$this->i] = $this->s[$this->j];
			$this->s[$this->j] = $t;

			$t = ($this->s[$this->i] + $this->s[$this->j]) % $keyLen;

			$str{$c} = chr(ord($str{$c}) ^ $this->s[$t]);
		}
		return $str;
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
		$key =& $this->secret;
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