<?
/*
Copyright (c) 2005-2007, Rasmus Andersson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
/**
 * MCrypt based cipher implementation
 * 
 * <b>Note:</b> this class relies on the <samp>mcrypt</samp> extension. If you don't 
 * have it, consider using {@link BlowfishCompatCipher} instead.
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 * @ignore
 */
class MCryptCipherImpl extends Cipher {
	
	/**
	 * Initialization Vector
	 * @var string
	 */
	public $iv;
	
	/**
	 * MCrypt module instance
	 * @var resource
	 */
	protected $r;

	/** @var string */
	protected $key;
	
	/**
	 * 0 = uninited, 1 = encrypt, 2 = decrypt
	 * @var int
	 */
	private $cryptState = 0;
	
	
	/**
	 * @param mixed
	 * @param mixed
	 * @param string
	 * @param bool
	 */
	public function __construct($algorithm, $mode, $key=null, $iv=null)
	{
		$this->r = mcrypt_module_open($algorithm, '', $mode, '');
		if($iv !== false)
			$this->iv = substr($iv ? $iv : Cipher::$defaultIV, 0, mcrypt_enc_get_iv_size($this->r));
		if($key)
			$this->setKey($key);
	}
	
	/**
	 * @param  string
	 * @return void
	 */
	public function setKey($key) {
		$this->key = substr($key, 0, mcrypt_enc_get_key_size($this->r));
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
		@mcrypt_module_close($this->r);
	}
	
	/**
	 * Test all available algorithms in all available modes
	 * @ignore
	 */
	public static function __test() {
		# prevent auto-tests from running if mcrypt isnt installed
		if(!defined('MCRYPT_RAND'))
			return;
			
		$key = 'tr7raxe5apHeTr2v';
		
		# test all algos and all supported modes for each algo
		foreach(mcrypt_list_algorithms() as $a)
			foreach(mcrypt_list_modes() as $m)
				if(@mcrypt_get_block_size($a,$m))
					parent::__test(new self($a, $m, $key));
	}
}
?>