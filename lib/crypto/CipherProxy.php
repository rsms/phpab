<?
/**
 * Base class for backend auto-adapting ciphers
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 */
abstract class CipherProxy extends Cipher {
	
	/**
	 * Implementation
	 * @var Cipher
	 */
	public $impl;
	
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
		return $this->impl->decrypt($data);
	}
	
	/** @ignore */
	public static function __test($o=null) {
		parent::__test($o);
	}
}
?>
