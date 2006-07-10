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
	 * Initialization Vector
	 * @var string
	 */
	public $iv = null;
	
	/**
	 * MCrypt module instance
	 * @var resource
	 */
	protected $r = null;
	
	/**
	 * 0 = uninited, 1 = encrypt, 2 = decrypt
	 * @var int
	 */
	private $cryptState = 0;
	
	
	/**
	 * @param string 56 bytes
	 * @param string 8 bytes
	 */
	public function __construct($key, $iv = null)
	{
		if(strlen($key) != 56)
			throw new IllegalArgumentException('Key must be exactly 56 bytes long');
		
		$this->r = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');
		
		if($iv == null)
			$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->r), MCRYPT_RAND);
		elseif(strlen($iv) != 8)
			throw new IllegalArgumentException('Initialization Vector must be exactly 8 bytes long');
		
		$this->iv = $iv;
		$this->key = $key;
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	public function encrypt($data) {
		if($this->cryptState != 1) {
			@mcrypt_generic_deinit($this->r);
			mcrypt_generic_init($this->r, $this->key, $this->iv);
			$this->cryptState = 1;
		}
		return mcrypt_generic($this->r, $data);
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
}
?>