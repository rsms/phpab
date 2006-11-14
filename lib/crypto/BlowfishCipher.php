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
 * 
 * <b>Note:</b> this class relies on the <samp>mcrypt</samp> extension. If you don't 
 * have it, consider using {@link BlowfishCompatCipher} instead.
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 */
class BlowfishCipher extends Cipher {
	
	/**
	 * Implementation
	 * @var Cipher
	 */
	public $impl;
	
	
	/**
	 * @param string 56 bytes
	 * @param string 8 bytes
	 */
	public function __construct($key, $iv = null)
	{
		
	}
	
	/**
	 * @param  string
	 * @return void
	 */
	public function setKey($key) {
		$this->impl->setKey($key);
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	public function encrypt($data) {
		return $this->impl->encrypt($data);
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	public function decrypt($data) {
		if($this->cryptState != 2) {
			@mcrypt_generic_deinit($this->r);
			mcrypt_generic_init($this->r, $this->key, $this->iv);
			$this->cryptState = 2;
		}
		return trim(mdecrypt_generic($this->r, $data), "\0");
	}
	
	/** @return void */
	public function __destruct() {
		@mcrypt_generic_deinit($this->r);
		mcrypt_module_close($this->r);
	}
	
	/** @ignore */
	public static function __test() {
		parent::__test(new self('tr7raxe5apHeTr2v'));
	}
}
?>