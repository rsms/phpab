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
	 * Electronic CodeBook is suitable for random data, such as encrypting other keys.
	 * Since data there is short and random, the disadvantages of ECB have a favorable 
	 * negative effect.
	 * 
	 * This constant is compatible with mcrypt, if installed.
	 */
	const MODE_ECB = 1;
	
	/**
	 * Cipher Block Chaining is especially suitable for encrypting files where the 
	 * security is increased over ECB significantly.
	 * 
	 * This constant is compatible with mcrypt, if installed.
	 */
	const MODE_CBC = 2;
	
	/**
	 * Cipher Feedback is the best mode for encrypting byte streams where single bytes 
	 * must be encrypted.
	 * 
	 * This constant is compatible with mcrypt, if installed.
	 */
	const MODE_CFB = 3;
	
	/**
	 * Output FeedBack, in 8bit, is comparable to CFB, but can be used in applications 
	 * where error propagation cannot be tolerated. It's insecure (because it operates 
	 * in 8bit mode) so it is not recommended to use it.
	 * 
	 * This constant is compatible with mcrypt, if installed.
	 */
	const MODE_OFB = 4;
	
	/**
	 * Output FeedBack, in N bit, is comparable to OFB, but more secure because it 
	 * operates on the block size of the algorithm.
	 * 
	 * This constant is compatible with mcrypt, if installed.
	 */
	const MODE_NOFB = 5;
	
	/**
	 * Stream is an extra mode to include some stream algorithms like WAKE or RC4.
	 * 
	 * This constant is compatible with mcrypt, if installed.
	 */
	const MODE_STREAM = 6;
	
	
	/** @var string */
	public static $defaultIV = "\x9E\x37\x79\xB9abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

	/**
	 * @param  string
	 * @return void
	 */
	abstract public function setKey($key);
	
	/**
	* Encrypt data
	*
	* @param   string  Data to encrypt
	* @return  string  Encrypted data
	*/
	abstract public function encrypt( $data );
	
	/**
	* Decrypt data
	*
	* @param   string  Data to decrypt
	* @return  string  Decrypted data
	*/
	abstract public function decrypt( $data );
	
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
	
	/**
	 * Returns a string rep for use in mcrypt for the specified mode.
	 * 
	 * @param  int
	 * @return string
	 */
	protected static function mcryptModeForMode($mode) {
		switch($mode) {
			case self::MODE_ECB: return MCRYPT_MODE_ECB;
			case self::MODE_CBC: return MCRYPT_MODE_CBC;
			case self::MODE_CFB: return MCRYPT_MODE_CFB;
			case self::MODE_OFB: return MCRYPT_MODE_OFB;
			case self::MODE_NOFB: return MCRYPT_MODE_NOFB;
			case self::MODE_STREAM: return MCRYPT_MODE_STREAM;
		}
		throw new IllegalStateException('No matching type for mcrypt');
	}
	
	/** @ignore */
	public static function __test($instance = null)
	{
		if($instance) {
			$dataClear = 'Hello World';
			assert(strlen($dataEncrypted = $instance->encrypt($dataClear)));
			assert($dataEncrypted != $dataClear);
			assert(strlen($dataClearAgain = $instance->decrypt($dataEncrypted)));
			assert($dataClearAgain != $dataEncrypted);
			assert($dataClearAgain == $dataClear);
		}
	}
}
?>