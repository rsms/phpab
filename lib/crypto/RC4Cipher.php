<?
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