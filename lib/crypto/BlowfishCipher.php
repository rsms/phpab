<?
/**
 * Blowfish cipher
 * 
 * Blowfish is a symmetric block cipher that can be used as a drop-in replacement 
 * for DES or IDEA. It takes a variable-length key, from 32 bits to 448 bits, making 
 * it ideal for both domestic and exportable use.
 * 
 * Blowfish was designed in 1993 by Bruce Schneier as a fast, free alternative to 
 * existing encryption algorithms. Since then it has been analyzed considerably, and 
 * it is slowly gaining acceptance as a strong encryption algorithm.
 * 
 * Blowfish is unpatented and license-free, and is available free for all uses.
 * It has some known weaknesses.
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 */
class BlowfishCipher extends CipherProxy {
	
	/**
	 * Block mode
	 */
	const MODE_ECB = 0;
	
	/**
	 * Block mode
	 */
	const MODE_CBC = 1;
	
	/**
	 * @param string 56 bytes
	 * @param int
	 */
	public function __construct( $key=null, $iv=null, $mode=self::MODE_ECB )
	{
		if(defined('MCRYPT_RAND')) {
			$this->impl = new MCryptCipherImpl(MCRYPT_BLOWFISH, 
				($mode == self::MODE_CBC) ? MCRYPT_MODE_CBC : MCRYPT_MODE_ECB, $key, $iv);
		}
		else {
			$this->impl = new BlowfishCipherImpl($mode, $key, $iv);
		}
	}
	
	/** @ignore */
	public static function __test() {
		parent::__test(new self('tr7raxe5apHeTr2v'));
	}
}
?>