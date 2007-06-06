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
		$exc = null;
		
		if($this->log)
			$this->log->warn("Testing class ".$this->getClassInfo()->getPackageName().".$this->class ... ");
		
		try {
			call_user_func(array($this->class, '__test'));
		}
		catch(Exception $e) {
			$this->exception = $e;
			$exc = $e;
		}
		
		if($this->log)
			$this->log->warn(($this->passed() ? 'PASSED':'FAILED')."\n");
		
		if($exc)
			throw $exc;
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