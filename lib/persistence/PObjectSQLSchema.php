<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage persistence
 */
class PObjectSQLSchema extends PObjectSchema {
	
	/** @var string */
	public $sql;
	
	/** @var PObjectSQLStorage */
	protected $storage;
	
	/**
	 * @param  array
	 * @param  string
	 * @param  string
	 */
	public function __construct(&$fields, $pk, $sql, PObjectSQLStorage $storage) {
		parent::__construct($fields, $pk);
		$this->sql = $sql;
		$this->storage = $storage;
	}
	
	/** @return void */
	public function execute() {
		$this->storage->dbexec($this->sql);
	}
	
	/** @return string */
	public function toString() {
		return $this->sql;
	}
}
?>