<?
/**
 * XTEA cipher.
 * eXtended TEA - a safer version of TEA.
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 * @see        TEA
 * @see        XXTEA
 */
class XTEACipher extends CipherProxy {
	
	/**
	 * @param string 16 bytes
	 * @param int
	 */
	public function __construct( $key=null, $iv=null, $mode=Cipher::MODE_CBC )
	{
		if(defined('MCRYPT_XTEA'))
			$this->impl = new MCryptCipherImpl(MCRYPT_XTEA, Cipher::mcryptModeForMode($mode), $key, $iv);
		else
			throw new IllegalStateException('No implementation of XTEA is available');
	}
	
	/** @ignore */
	public static function __test() {
		parent::__test(new self('tr7raxe5apHeTr2v'));
	}
}
?>