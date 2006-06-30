<?

# Flags
define('CIPH_BASE64ENCODE', 1);
define('CIPH_HEXENCODE',	2);

/**
 * Encryption Block Mode constants
 *
 * @const MODE_ECB ecb mode
 * @const MODE_CBC cbc mode
 */
define("CIPH_MODE_ECB", 4);
define("CIPH_MODE_CBC", 8);

/**
 * Cipher baseclass
 *
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 */
abstract class Cipher {
	
	/**
	 * Raw encoding (data is not encoded)
	 */
	const ENCODE_RAW = 0;
	
	/**
	 * Hex encoding
	 */
	const ENCODE_BASE16 = 1;
	
	/**
	 * Base64 encoding
	 */
	const ENCODE_BASE64 = 2;
	
	
	/**
	 * Encryption Block Mode
	 */
	const MODE_ECB = 4;
	
	/**
	 * Encryption Block Mode
	 */
	const MODE_CBC = 8;
	
	
	/**
	 * @var int
	 */
	protected $encoding = 2;
	
	/**
	 * @var int bits
	 */
	protected $secretLength = 512;
	
	/**
	 * @var string
	 */
	protected $secret = null;
	

	/**
	 * @param  string
	 * @return void
	 */
	public function setSecret($secret) {
		$this->secret = $secret;
	}
	
	/**
	 * @return string
	 */
	protected function getSecret() {
		return $this->secret;
	}
	
	/**
	 * @param  int
	 * @return void
	 */
	public function setEncoding( $constant ) {
		$this->encoding = $constant;
	}
	
	/**
	 * @return  int
	 */
	protected function getEncoding() {
		return $this->encoding;
	}
	
	/**
	 * Key "strength". Size in bits. Set to even octals. Try to pass a key of the appropriate length.
	 * Ie: if you specify 512 bits key strength, use a 64 character key. (512/8=64)
	 *
	 * @param  int  bits
	 * @return void
	 */
	public function setKeyLength ( $len ) {
		$this->keyLength = $len;
	}
	
	/**
	 * @return int  bits
	 */
	public function getKeyLength ( $len ) {
		return $this->keyLength;
	}
	
	/**
	* Encrypt data
	*
	* @param   string  Data to encrypt
	* @return  string  Encrypted data
	*/
	public function encrypt ( $data ) {
		return '';
	}
	
	/**
	* Decrypt data
	*
	* @param   string  Data to decrypt
	* @return  string  Decrypted data
	*/
	public function decrypt ( $data ) {
		return '';
	}
	
	/**
	 * Encrypt a file
	 *
	 * @param  string  File to encrypt
	* @return  string  Encrypted data
	 */
	public function encryptFile ( $file ) {
		return $this->encrypt(file_get_contents($file));
	}
	
	/**
	 * Decrypt a file
	 *
	 * @param  string  File to decrypt
	 * @return string  Decrypted data
	 */
	public function decryptFile ( $file ) {
		return $this->decrypt(file_get_contents($file));
	}
	
	
	/// PRIVATES ///////////////////////////////////////////////
	
	/**
	 * Apply any encoding and return the filtered data.
	 * Used by encrypt.
	 *
	 * @param  string
	 * @return string
	 */
	protected function postFilterData( &$data )
	{	
		if ($this->hasFlag(self::ENCODE_BASE64))
			return base64_encode($data);
		elseif ($this->hasFlag(self::ENCODE_BASE16))
			return bin2hex($data);
		else
			return $data;
	}
	
	
	/**
	 * Apply any encoding and return the filtered data.
	 * Used by decrypt.
	 *
	 * @param  string
	 * @return string
	 */
	protected function preFilterData( &$data )
	{	
		if ($this->flags & self::ENCODE_BASE64)
			return base64_decode($data);
		elseif ($this->hasFlag(self::ENCODE_BASE16))
			return self::hex2bin($data);
		else
			return $data;
	}
	
	
	/**
	 *  Convert a hexadecimal string to a binary string (e.g. convert "616263" to "abc").
	 *
	 *  @param  string  $str	Hexadecimal string to convert to binary string.
	 *  @return string		  Binary string.
	 */
	protected static function hex2bin ( $str ) {
		return pack('H'.strlen($str), $str);
	}


}
?>