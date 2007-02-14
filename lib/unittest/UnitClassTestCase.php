<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitClassTestCase extends UnitTestCase
{
	/** @var string */
	protected $class = '';
	
	/** @var ABReflectionClass */
	protected $classInfo = null;
	
	/**
	 * @param string
	 * @param ABReflectionClass
	 */
	public function __construct($class, $classInfo = null)
	{
		$this->class = $class;
		$this->classInfo = $classInfo;
	}
	
	/**
	 * Implementation-specific test logic
	 *
	 * @return void
	 */
	protected function performTests()
	{
		if($this->log)
			$this->log->warn("Testing class ".$this->getClassInfo()->getPackageName().".$this->class ... ");
		call_user_func(array($this->class, '__test'));
		if($this->log)
			$this->log->warn(($this->passed() ? 'PASSED':'FAILED')."\n");
	}
	
	/**
	 * Class name
	 *
	 * @return string
	 * @see    getClassInfo()
	 */
	public function getClass()
	{
		return $this->class;
	}
	
	/**
	 * Class information
	 *
	 * @return ABReflectionClass
	 * @see    getClass()
	 */
	public function getClassInfo()
	{
		if($this->classInfo === null)
			$this->classInfo = new ABReflectionClass($this->class);
		return $this->classInfo;
	}
}
?>