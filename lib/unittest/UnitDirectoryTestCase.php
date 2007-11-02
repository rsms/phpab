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
 * Perform tests on all php-files in a directory
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitDirectoryTestCase extends UnitTestCase
{
	/** @var string */
	protected $path = '';
	
	/** @var bool */
	protected $recursive = false;
	
	/** @var UnitTestCase[] */
	protected $cases = array();
	
	/** @var bool */
	private $allCasesPassed = true;
	
	
	/**
	 * @param string
	 * @param bool
	 */
	public function __construct($path, $recursive = false) {
		$this->path = $path;
		$this->recursive = $recursive;
	}
	
	/**
	 * @return UnitTestCase[]
	 */
	public function getCompletedTestCases() {
		return $this->cases;
	}
	
	/**
	 * Run tests
	 *
	 * @return void
	 */
	public function test()
	{
		# Refresh assert bindings
		UnitTest::instance()->refreshAssertBindings();
		
		# Reset case storage & passed-cache
		$this->cases = array();
		$this->allCasesPassed = true;
		
		# Implementation-specific test logic
		$this->performTests();
	}
	
	
	/**
	 * Implementation-specific test logic
	 *
	 * @return void
	 */
	protected function performTests()
	{
		# Import all php-files
		$files = array();
		$this->findPHPFiles($this->path, $files);
		
		# Find loaded classes and run UnitClassTestCase tests
		foreach($files as $file)
		{
			# Create and execute test
			$this->executeTest(new UnitFileTestCase($files));
		}
	}
	
	/**
	 * @param  string
	 * @param  array
	 * @return void
	 */
	protected function findPHPFiles($path, &$files)
	{
		foreach(scandir($path) as $file)
		{
			if($file{0} == '.')
				continue;
			
			$filepath = $path.'/'.$file;
			
			if(strrchr($file, '.') == '.php')
			{
				$files[] = $filepath;
			}
			elseif($this->recursive && is_dir($filepath) && is_readable($filepath))
			{
				$this->findPHPFiles($filepath, $files);
			}
		}
	}
	
	/**
	 * Execute one subcase
	 *
	 * Associates the test with this case, then runs it and finally updates 
	 * $this->allCasesPassed depending on the result of $case->passed()
	 * 
	 * @param  UnitTestCase
	 * @return void
	 */
	protected function executeTest(UnitTestCase $case)
	{
		$case->log = $this->log;
		$this->cases[] = $case;
		$case->test();
		
		if($this->allCasesPassed && !$case->passed()) {
			$this->allCasesPassed = false;
		}
	}
	
	
	/** @return bool */
	public function passed() {
		return $this->allCasesPassed;
	}
}
?>