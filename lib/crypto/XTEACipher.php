<?
/**
 * XTEA cipher.
 * eXtended TEA - a safer version of TEA.
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 */
class XTEACipher extends CipherProxy {
	
	/**
	 * Block mode
	 */
	const MODE_ECB = 0;
	
	/**
	 * Block mode
	 */
	const MODE_CBC = 1;
	
	/**
	 * @param string 16 bytes
	 * @param int
	 */
	public function __construct( $key=null, $iv=null, $mode=self::MODE_ECB )
	{
		if(defined('MCRYPT_RAND')) {
			$this->impl = new MCryptCipherImpl(MCRYPT_XTEA, 
				($mode == self::MODE_CBC) ? MCRYPT_MODE_CBC : MCRYPT_MODE_ECB, $key, $iv);
		}
		else {
			throw new IllegalStateException('No implementation of XTEA is available');
		}
	}
	
	/** @ignore */
	public static function __test() {
		parent::__test(new self('tr7raxe5apHeTr2v'));
	}
}
?>