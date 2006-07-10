<?
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
	 * @var int
	 */
	protected $encoding = 2;
	
	/**
	 * @var string
	 */
	protected $key = null;
	

	/**
	 * @param  string
	 * @return void
	 */
	public function setKey($key) {
		$this->key = $key;
	}
	
	/**
	 * @return string
	 */
	protected function getKey() {
		return $this->key;
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
	* Encrypt data
	*
	* @param   string  Data to encrypt
	* @return  string  Encrypted data
	*/
	public function encrypt( $data ) {
		return '';
	}
	
	/**
	* Decrypt data
	*
	* @param   string  Data to decrypt
	* @return  string  Decrypted data
	*/
	public function decrypt( $data ) {
		return '';
	}
	
	/**
	 * Encrypt a file
	 *
	 * Default implementation reads whole file into memory and passes it to encrypt.
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
	 * Default implementation reads whole file into memory and passes it to decrypt.
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
		switch($this->encoding) {
			case self::ENCODE_BASE64: return base64_encode($data);
			case self::ENCODE_BASE16: return bin2hex($data);
			default:                  return $data;
		}
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
		switch($this->encoding) {
			case self::ENCODE_BASE64: return base64_decode($data);
			case self::ENCODE_BASE16: return pack('H'.strlen($data), $data);
			default:                  return $data;
		}
	}
	
	
	/** @ignore */
	public static function __test($instance = null)
	{
		if($instance) {
			$dataClear = 'En kanin hittade en rotliknande sork';
			assert(strlen($dataEncrypted = $instance->encrypt($dataClear)));
			assert($dataEncrypted != $dataClear);
			assert(strlen($dataClearAgain = $instance->decrypt($dataEncrypted)));
			assert($dataClearAgain != $dataEncrypted);
			assert($dataClearAgain == $dataClear);
		}
	}
}
?>