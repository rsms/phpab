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
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
abstract class UnitTestCase
{
	/** @var SimpleLogger */
	public $log = null;
	
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