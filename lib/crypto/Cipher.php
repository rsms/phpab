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