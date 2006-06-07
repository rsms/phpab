<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage db
 */
class DBException extends ABException {
	
	/** @var string  Named like this to match SQLiteException */
	public $errorInfo = '';
	
	/**
	 * @param  string
	 * @param  int
	 * @param  string
	 */
	public function __construct($msg = null, $errno = 0, $error_info = '') {
		parent::__construct($msg, $errno, null, -1, null);
		$this->errorInfo = $error_info;
	}
}
?>