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
class UnitTest
{
	/** @var SimpleLogger */
	public $log = null;
	
	/** @var UnitTest */
	protected static $instance = null;
	
	/** @var UnitTestAssertion[] */
	protected $assertions = array();
	
	
	/** @return UnitTest */
	public static function instance() {
		if(!self::$instance)
			self::$instance = new self;
		return self::$instance;
	}
	
	/** Singleton */
	protected function __construct() {
		$this->refreshAssertBindings();
	}
	
	/**
	 * @param  string
	 * @param  int
	 * @param  string
	 */
	public function assertCallback( $file, $line, $message )
	{
		$message = trim($message);
		$this->assertions[] = new UnitTestAssertion($line, $message ? ereg_replace('^.*//\*', '', $message) : '');
	}
	
	/** @return void */
	public function refreshAssertBindings() {
		assert_options(ASSERT_CALLBACK, array($this, 'assertCallback'));
		assert_options(ASSERT_ACTIVE, 1);
		assert_options(ASSERT_WARNING, 0);
		assert_options(ASSERT_QUIET_EVAL, 1);
		assert_options(ASSERT_BAIL, 0);
	}
	
	/**
	 * @return array
	 */
	public function &getAssertionStorage() {
		return $this->assertions;
	}
	
	/**
	 * @param  array
	 * @return void
	 */
	public function setAssertionStorage(&$storage) {
		$this->assertions =& $storage;
	}
	
	/**
	 * Run tests on all available php classes in dir $path.
	 *
	 * @param  string
	 * @param  bool    Include subdirectories
	 * @return UnitClassTestCase[]
	 */
	public function testLibrary($path, $recursive = false)
	{
		$this->refreshAssertBindings();
		$classes = get_declared_classes();
		$this->importFilesInDir($path, $recursive, '/^[A-Z]/', true);
		
		# find out which classes were loaded
		$classes = array_diff(get_declared_classes(), $classes);
		
		# test them!
		$cases = array();
		
		foreach($classes as $class) {
			if(is_callable(array($class, '__test'), false))
			{
				$case = new UnitClassTestCase($class);
				$case->log = $this->log;
				$case->test($this);
				$cases[] = $case;
			}
		}
		
		return $cases;
	}
	
	/** @return void */
	private function importFilesInDir($path, $recursive, $filenameFilterRE = null, $checkClasses = false)
	{
		foreach(scandir($path) as $file)
		{
			if($file{0} == '.')
				continue;
			
			$filepath = $path.'/'.$file;
			if(strrchr($file, '.') == '.php')
			{
				if($filenameFilterRE && !preg_match($filenameFilterRE, $file))
					continue;
				
				if($checkClasses) {
					$guessedClass = substr($file, 0, -4);
					if(class_exists($guessedClass)) {
						continue;
					}
				}
				
				require_once $filepath;
			}
			elseif($recursive && is_dir($filepath) && is_readable($filepath)) {
				PHP::addClasspath($filepath);
				$this->importFilesInDir($filepath, $recursive, $filenameFilterRE, $checkClasses);
			}
		}
	}
}
?>