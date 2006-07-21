<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage persistence
 */
class PObjectSchema {
	
	/** @var array (string name => array('type' => string(3), 'default' => mixed), ... ) */
	public $fields;
	
	/** @var string */
	public $primaryKey;
	
	/**
	 * @param  array
	 * @param  string
	 * @param  string
	 */
	public function __construct(&$fields, $pk) {
		$this->fields =& $fields;
		$this->primaryKey = $pk;
	}
	
	/** @return void */
	public function execute() {
		throw new IllegalOperationException('Not implemented');
	}
	
	/** @return string */
	public function toString() {
		return get_class($this).'{'.print_r($this->fields, 1).'}';
	}
	
	/** @return string */
	public function __toString() { return $this->toString(); }
}
?>