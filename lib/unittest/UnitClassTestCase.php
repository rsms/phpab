<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitClassTestCase extends UnitTestCase {
	
	/** @var string */
	protected $class = '';
	
	/** @var ReflectionClass */
	protected $classInfo = null;
	
	/**
	 * @param string
	 * @param ReflectionClass
	 */
	public function __construct($class, $classInfo = null) {
		$this->class = $class;
		$this->classInfo = $classInfo;
	}
	
	/**
	 * Implementation-specific test logic
	 *
	 * @return void
	 */
	protected function performTests() {
		call_user_func(array($this->class, '__test'));
	}
	
	/**
	 * Class name
	 *
	 * @return string
	 * @see    getClassInfo()
	 */
	public function getClass() {
		return $this->class;
	}
	
	/**
	 * Class information
	 *
	 * @return ReflectionClass
	 * @see    getClass()
	 */
	public function getClassInfo() {
		if($this->classInfo === null)
			$this->classInfo = new ReflectionClass($this->class);
		return $this->classInfo;
	}
}
?>