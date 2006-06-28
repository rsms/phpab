<?
/**
 * Perform tests in a plain php-file
 * 
 * <b>Example file:</b>
 * <code>
 * function shake_me_baby($str) {
 *   return strrev($str);
 * }
 *
 * $users = array('Johan', 'Eva', 'Kalle');
 *
 * assert('shake_me_baby("Johan") == "nahoJ"');
 * assert('shake_me_baby("Killer") != "Potatis"');
 * assert('in_array("Eva", $users)');
 * assert('!in_array("Sunkarn", $users)');
 * assert('ucfirst($users[0]) == $users[0]');
 * </code>
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitFileTestCase extends UnitTestCase {
	
	/** @var string */
	protected $path = '';
	
	/** @param string */
	public function __construct($path) {
		$this->path = $path;
	}
	
	/** @return void */
	protected function performTests() {
		require_once $this->path;
	}
	
	/**
	 * File path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
}
?>