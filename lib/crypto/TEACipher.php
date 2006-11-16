<?
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