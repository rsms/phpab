<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
abstract class UnitTestCase {
	
	/** @var UnitTestAssertion[] */
	protected $assertions = array();
	
	/** @var Exception */
	protected $exception = null;
	
	
	/**
	 * Run tests
	 *
	 * @return void
	 */
	public function test()
	{
		$oldAssertionStorage =& UnitTest::instance()->getAssertionStorage();
		UnitTest::instance()->setAssertionStorage($this->assertions);
		
		try {
			$this->performTests();
		}
		catch(Exception $e) {
			$this->exception = $e;
		}
		
		UnitTest::instance()->setAssertionStorage($oldAssertionStorage);
		$this->updateAssertions();
	}
	
	/**
	 * Implementation-specific test logic
	 *
	 * Implement this method in your subclasses to execute 
	 * the actual assert()'s.
	 *
	 * @return void
	 */
	abstract protected function performTests();
	
	
	/** @return bool */
	public function passed() {
		return count($this->assertions) == 0 && !$this->hasException();
	}
	
	/**
	 * @return bool
	 */
	public function hasException() {
		return $this->exception ? true : false;
	}
	
	/**
	 * @return Exception
	 */
	public function getException() {
		return $this->exception;
	}
	
	/**
	 * @return UnitTestAssertion[]
	 */
	public function getAssertions() {
		return $this->assertions;
	}
	
	/**
	 * @param  UnitTestAssertion
	 * @return void
	 */
	public function addAssertion(UnitTestAssertion $assertion) {
		$assertion->testCase = $this;
		$this->assertions[] = $assertion;
	}
	
	/**
	 * @param  UnitTestAssertion[]
	 * @return void
	 */
	public function addAssertions($assertions) {
		foreach($assertions as $a)
			$this->addAssertion($a);
	}
	
	/**
	 * @param  UnitTestAssertion[]
	 * @return void
	 */
	public function setAssertions($assertions) {
		$this->assertions = $assertions;
	}
	
	/**
	 * Update instance associations of exisiting assertions
	 * 
	 * @return void
	 */
	protected function updateAssertions() {
		foreach($this->assertions as $a)
			$a->testCase = $this;
	}
}
?>